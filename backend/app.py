from flask import Flask, render_template, request, jsonify, session, redirect, url_for
from flask_sqlalchemy import SQLAlchemy
from werkzeug.security import generate_password_hash, check_password_hash
import openpyxl
import os
from datetime import datetime
import pandas as pd
from config import get_config
from utils import (
    validate_excel_file, 
    read_excel_file, 
    normalize_search_text,
    search_in_excel_data,
    format_search_results,
    allowed_file
)

# Configurar rutas absolutas
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
FRONT_DIR = os.path.join(BASE_DIR, 'front')

# Si FRONT_DIR no existe (en producción), intentar rutas relativas
if not os.path.exists(FRONT_DIR):
    FRONT_DIR = os.path.join(os.path.dirname(__file__), '..', 'front')

app = Flask(__name__, template_folder=FRONT_DIR, static_folder=FRONT_DIR)
app.config.from_object(get_config())

# Agregar headers CORS para que el navegador pueda hacer peticiones
@app.after_request
def add_cors_headers(response):
    response.headers['Access-Control-Allow-Origin'] = '*'
    response.headers['Access-Control-Allow-Methods'] = 'GET, POST, PUT, DELETE, OPTIONS'
    response.headers['Access-Control-Allow-Headers'] = 'Content-Type, Authorization'
    return response

# Crear carpeta de uploads si no existe
if not os.path.exists(app.config['UPLOAD_FOLDER']):
    os.makedirs(app.config['UPLOAD_FOLDER'])

db = SQLAlchemy(app)

# Modelo de Usuario
class User(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(80), unique=True, nullable=False)
    password = db.Column(db.String(120), nullable=False)
    role = db.Column(db.String(20), default='user')  # 'admin' o 'user'
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

    def set_password(self, password):
        self.password = generate_password_hash(password)

    def check_password(self, password):
        return check_password_hash(self.password, password)
    
    def is_admin(self):
        return self.role == 'admin'

# Modelo de Log de Subidas
class UploadLog(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    filename = db.Column(db.String(255), nullable=False)
    username = db.Column(db.String(80), nullable=False)
    upload_date = db.Column(db.DateTime, default=datetime.utcnow)
    total_records = db.Column(db.Integer, nullable=False)
    
    def to_dict(self):
        return {
            'id': self.id,
            'filename': self.filename,
            'username': self.username,
            'upload_date': self.upload_date.strftime('%Y-%m-%d %H:%M:%S'),
            'total_records': self.total_records
        }

# Variable global para almacenar datos del Excel
current_data = []
current_columns = []

def get_current_user():
    """Helper para obtener el usuario actual de la sesión"""
    if 'user_id' not in session:
        return None
    try:
        user_id = int(session['user_id']) if isinstance(session['user_id'], str) else session['user_id']
        return User.query.get(user_id)
    except:
        return None

def load_excel_data(filepath):
    """Carga los datos del archivo Excel a memoria con validación de duplicados"""
    global current_data, current_columns
    try:
        current_columns, excel_data = read_excel_file(filepath)
        
        if not excel_data or not current_columns:
            return False
        
        # Validar duplicados: cédula + comparendo
        seen_combinations = set()
        filtered_data = []
        duplicates_found = []
        
        # Buscar índices de CEDULA y COMPARENDO en los headers
        cedula_idx = None
        comparendo_idx = None
        
        for i, col in enumerate(current_columns):
            col_upper = col.upper().strip()
            if 'CEDULA' in col_upper or 'CÉDULA' in col_upper:
                cedula_idx = i
            elif 'COMPARENDO' in col_upper:
                comparendo_idx = i
        
        # Si encontramos ambas columnas, validar
        if cedula_idx is not None and comparendo_idx is not None:
            for row_idx, row_data in enumerate(excel_data, start=2):  # Empieza en 2 porque fila 1 son headers
                if len(row_data) > max(cedula_idx, comparendo_idx):
                    cedula = str(row_data[cedula_idx]).strip()
                    comparendo = str(row_data[comparendo_idx]).strip()
                    
                    combination = (cedula, comparendo)
                    
                    if combination in seen_combinations:
                        duplicates_found.append({
                            'row': row_idx,
                            'cedula': cedula,
                            'comparendo': comparendo
                        })
                    else:
                        seen_combinations.add(combination)
                        filtered_data.append(row_data)
                else:
                    filtered_data.append(row_data)
        else:
            filtered_data = excel_data
        
        current_data = filtered_data
        
        # Guardar información de duplicados si los hay
        if duplicates_found:
            print(f"⚠️ Advertencia: Se encontraron {len(duplicates_found)} registros duplicados (misma cédula + comparendo)")
            for dup in duplicates_found[:5]:  # Mostrar primeros 5
                print(f"   - Fila {dup['row']}: Cédula {dup['cedula']}, Comparendo {dup['comparendo']}")
        
        return bool(current_columns and current_data)
    except Exception as e:
        print(f"Error loading Excel: {e}")
        return False

def search_data(search_text):
    """Busca en los datos del Excel por cualquier campo"""
    if not current_data:
        return []
    
    results = search_in_excel_data(current_data, current_columns, search_text)
    return results

# Crear tabla de base de datos
with app.app_context():
    db.create_all()

# Rutas

@app.route('/')
def index():
    """Página principal de búsqueda"""
    if 'user_id' not in session:
        return redirect(url_for('login'))
    return redirect(url_for('home'))

@app.route('/home')
def home():
    """Página de inicio/home"""
    if 'user_id' not in session:
        return redirect(url_for('login'))
    
    user = get_current_user()
    
    # Si es admin, mostrar home con opciones
    if user and user.is_admin():
        with open(os.path.join(FRONT_DIR, 'home.html'), 'r', encoding='utf-8') as f:
            return f.read()
    else:
        # Si es usuario regular, redirigir a búsqueda
        return redirect(url_for('search_page'))

@app.route('/search')
def search_page():
    """Página de búsqueda"""
    if 'user_id' not in session:
        return redirect(url_for('login'))
    # Servir el archivo index.html
    with open(os.path.join(FRONT_DIR, 'index.html'), 'r', encoding='utf-8') as f:
        return f.read()

@app.route('/login', methods=['GET', 'POST'])
def login():
    """Página de login"""
    if request.method == 'POST':
        data = request.get_json()
        username = data.get('username')
        password = data.get('password')
        
        user = User.query.filter_by(username=username).first()
        
        if user and user.check_password(password):
            session['user_id'] = user.id
            session['username'] = user.username
            session['is_admin'] = user.is_admin()
            return jsonify({'success': True, 'message': 'Inicio de sesión exitoso', 'is_admin': user.is_admin()}), 200
        else:
            return jsonify({'success': False, 'message': 'Usuario o contraseña incorrectos'}), 401
    
    # Servir el archivo login.html
    with open(os.path.join(FRONT_DIR, 'login.html'), 'r', encoding='utf-8') as f:
        return f.read()

@app.route('/register', methods=['POST'])
def register():
    """Crear nuevo usuario - Solo para admin"""
    if 'user_id' not in session:
        return jsonify({'success': False, 'message': 'No autorizado'}), 401
    
    try:
        user = get_current_user()
        if not user or not user.is_admin():
            return jsonify({'success': False, 'message': 'Solo administradores pueden crear usuarios'}), 403
        
        data = request.get_json()
        username = data.get('username')
        password = data.get('password')
        role = data.get('role', 'user')  # 'admin' o 'user'
        
        if not username or not password:
            return jsonify({'success': False, 'message': 'Usuario y contraseña requeridos'}), 400
        
        # Verificar si el usuario ya existe
        if User.query.filter_by(username=username).first():
            return jsonify({'success': False, 'message': 'El usuario ya existe'}), 400
        
        # Crear nuevo usuario
        new_user = User(username=username, role=role)
        new_user.set_password(password)
        
        db.session.add(new_user)
        db.session.commit()
        return jsonify({'success': True, 'message': 'Usuario creado exitosamente'}), 201
    except Exception as e:
        db.session.rollback()
        print(f"ERROR EN /register: {str(e)}", flush=True)
        import traceback
        traceback.print_exc()
        return jsonify({'success': False, 'message': f'Error al crear usuario: {str(e)}'}), 500

@app.route('/api/v1/users', methods=['GET'])
def api_users():
    """Obtener lista de usuarios - Solo para admin"""
    if 'user_id' not in session:
        return jsonify({'success': False, 'message': 'No autorizado'}), 401
    
    user = get_current_user()
    if not user or not user.is_admin():
        return jsonify({'success': False, 'message': 'Solo administradores pueden ver usuarios'}), 403
    
    users = User.query.all()
    users_list = [{'id': u.id, 'username': u.username, 'role': u.role, 'created_at': u.created_at.isoformat()} for u in users]
    
    return jsonify({'success': True, 'users': users_list}), 200

@app.route('/api/v1/change-password', methods=['POST'])
def api_change_password():
    """Cambiar contraseña de un usuario - Solo para admin"""
    if 'user_id' not in session:
        return jsonify({'success': False, 'message': 'No autorizado'}), 401
    
    try:
        user = get_current_user()
        if not user or not user.is_admin():
            return jsonify({'success': False, 'message': 'Solo administradores pueden cambiar contraseñas'}), 403
        
        data = request.get_json()
        username = data.get('username')
        new_password = data.get('password')
        
        if not username or not new_password:
            return jsonify({'success': False, 'message': 'Usuario y contraseña requeridos'}), 400
        
        target_user = User.query.filter_by(username=username).first()
        if not target_user:
            return jsonify({'success': False, 'message': 'Usuario no encontrado'}), 404
        
        target_user.set_password(new_password)
        db.session.commit()
        
        return jsonify({'success': True, 'message': f'Contraseña de {username} cambiada exitosamente'}), 200
    except Exception as e:
        db.session.rollback()
        print(f"ERROR EN /api/v1/change-password: {str(e)}", flush=True)
        import traceback
        traceback.print_exc()
        return jsonify({'success': False, 'message': f'Error al cambiar contraseña: {str(e)}'}), 500

@app.route('/api/v1/upload-logs', methods=['GET'])
def api_upload_logs():
    """Obtener logs de subidas - Solo para admin"""
    if 'user_id' not in session:
        return jsonify({'success': False, 'message': 'No autorizado'}), 401
    
    user = get_current_user()
    if not user or not user.is_admin():
        return jsonify({'success': False, 'message': 'Solo administradores pueden ver logs'}), 403
    
    # Obtener todos los logs ordenados por fecha más reciente
    logs = UploadLog.query.order_by(UploadLog.upload_date.desc()).all()
    logs_list = [log.to_dict() for log in logs]
    
    return jsonify({'success': True, 'logs': logs_list}), 200

@app.route('/api/v1/file/login', methods=['POST'])
def api_login():
    """API endpoint para login"""
    return login()

@app.route('/api/v1/file/search', methods=['GET'])
def api_search():
    """API endpoint para búsqueda"""
    if 'user_id' not in session:
        return jsonify({'success': False, 'message': 'No autorizado'}), 401
    
    search_text = request.args.get('searchText', '')
    
    if not search_text:
        return jsonify({'success': False, 'message': 'Texto de búsqueda requerido'}), 400
    
    results = search_data(search_text)
    formatted_results = format_search_results(current_columns, results)
    
    return jsonify(formatted_results), 200

@app.route('/api/v1/file/first-data', methods=['GET'])
def api_first_data():
    """API endpoint para obtener los primeros 50 datos"""
    if 'user_id' not in session:
        return jsonify({'success': False, 'message': 'No autorizado'}), 401
    
    if not current_data or not current_columns:
        # Retornar datos vacíos en vez de error 400
        return jsonify({'data': [], 'success': True}), 200
    
    # Obtener primeros 50 registros
    first_50 = current_data[:50]
    formatted_results = format_search_results(current_columns, first_50)
    
    return jsonify(formatted_results), 200

@app.route('/upload')
def upload_page():
    """Página de carga de archivos - Solo para admin"""
    if 'user_id' not in session:
        return redirect(url_for('login'))
    
    user = get_current_user()
    if not user or not user.is_admin():
        return jsonify({'success': False, 'message': 'Solo administradores pueden subir archivos'}), 403
    
    # Servir el archivo upload.html
    with open(os.path.join(FRONT_DIR, 'upload.html'), 'r', encoding='utf-8') as f:
        return f.read()

@app.route('/api/v1/file/upload', methods=['POST'])
def api_upload():
    """API endpoint para cargar archivos Excel - Solo para admin"""
    if 'user_id' not in session:
        return jsonify({'success': False, 'message': 'No autorizado'}), 401
    
    user = get_current_user()
    if not user or not user.is_admin():
        return jsonify({'success': False, 'message': 'Solo administradores pueden subir archivos'}), 403
    
    if 'file' not in request.files:
        return jsonify({'success': False, 'message': 'No se seleccionó archivo'}), 400
    
    file = request.files['file']
    
    if file.filename == '':
        return jsonify({'success': False, 'message': 'No se seleccionó archivo'}), 400
    
    if not allowed_file(file.filename, app.config['ALLOWED_EXTENSIONS']):
        return jsonify({'success': False, 'message': 'Solo se aceptan archivos Excel'}), 400
    
    try:
        # Guardar archivo
        filepath = os.path.join(app.config['UPLOAD_FOLDER'], file.filename)
        file.save(filepath)
        
        # Validar archivo
        if not validate_excel_file(filepath):
            os.remove(filepath)
            return jsonify({'success': False, 'message': 'El archivo no es un Excel válido'}), 400
        
        # Cargar datos a memoria
        if load_excel_data(filepath):
            # Informar cuántos registros se cargaron
            records_count = len(current_data)
            
            # Guardar en log de subidas
            try:
                upload_log = UploadLog(
                    filename=file.filename,
                    username=user.username,
                    total_records=records_count
                )
                db.session.add(upload_log)
                db.session.commit()
            except Exception as log_error:
                print(f"Error guardando log: {log_error}")
                db.session.rollback()
            
            message = f'Archivo subido y cargado exitosamente con {records_count} registros únicos'
            return jsonify({'success': True, 'message': message, 'records_loaded': records_count}), 200
        else:
            return jsonify({'success': False, 'message': 'Error al procesar el archivo'}), 500
    except Exception as e:
        print(f"ERROR EN /api/v1/file/upload: {str(e)}", flush=True)
        import traceback
        traceback.print_exc()
        return jsonify({'success': False, 'message': f'Error al subir archivo: {str(e)}'}), 500

@app.route('/logout')
def logout():
    """Logout del usuario"""
    session.clear()
    return redirect(url_for('login'))

if __name__ == '__main__':
    app.run(debug=True, host='localhost', port=5000)
