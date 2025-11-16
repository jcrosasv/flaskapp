#!/bin/bash

# Script de despliegue para AWS Lightsail
# Ejecutar como: bash deploy.sh

set -e

echo "======================================"
echo "Despliegue de Aplicación Flask"
echo "======================================"

# Variables
APP_DIR="/home/ubuntu/app"
APP_USER="ubuntu"
VENV_DIR="$APP_DIR/venv"

# 1. Crear directorio si no existe
echo "1. Creando directorios..."
mkdir -p $APP_DIR
mkdir -p /var/log/flask-app

# 2. Clonar o actualizar código (si está en Git)
echo "2. Descargando código..."
if [ -d "$APP_DIR/.git" ]; then
    cd $APP_DIR
    git pull origin main
else
    echo "⚠️  No es un repositorio Git. Asegúrate de subir el código manualmente."
fi

# 3. Crear virtual environment
echo "3. Creando ambiente virtual..."
python3 -m venv $VENV_DIR
source $VENV_DIR/bin/activate

# 4. Instalar dependencias
echo "4. Instalando dependencias..."
pip install --upgrade pip
pip install -r $APP_DIR/requirements.txt

# 5. Crear archivo .env
echo "5. Configurando variables de entorno..."
if [ ! -f "$APP_DIR/.env" ]; then
    cp $APP_DIR/.env.example $APP_DIR/.env
    echo "⚠️  IMPORTANTE: Edita .env y cambia SECRET_KEY"
fi

# 6. Crear base de datos
echo "6. Inicializando base de datos..."
cd $APP_DIR/backend
python3 <<EOF
from app import app, db
with app.app_context():
    db.create_all()
    print("✓ Base de datos creada")
EOF

# 7. Crear usuario admin
echo "7. Creando usuario admin..."
python3 <<EOF
from app import app, db, User
with app.app_context():
    if not User.query.filter_by(username='admin').first():
        admin = User(username='admin', role='admin')
        admin.set_password('admin123')
        db.session.add(admin)
        db.session.commit()
        print("✓ Usuario admin creado (username: admin, password: admin123)")
        print("⚠️  CAMBIAR CONTRASEÑA INMEDIATAMENTE EN PRODUCCIÓN")
    else:
        print("✓ Usuario admin ya existe")
EOF

# 8. Configurar Nginx
echo "8. Configurando Nginx..."
sudo cp $APP_DIR/deployment/nginx.conf /etc/nginx/sites-available/flask-app
sudo ln -sf /etc/nginx/sites-available/flask-app /etc/nginx/sites-enabled/flask-app
sudo rm -f /etc/nginx/sites-enabled/default
sudo systemctl restart nginx

# 9. Crear servicio systemd para Gunicorn
echo "9. Configurando servicio Gunicorn..."
sudo tee /etc/systemd/system/flask-app.service > /dev/null <<EOF
[Unit]
Description=Flask Application
After=network.target

[Service]
User=$APP_USER
Group=$APP_USER
WorkingDirectory=$APP_DIR/backend
Environment="PATH=$VENV_DIR/bin"
ExecStart=$VENV_DIR/bin/gunicorn \\
    --workers 4 \\
    --threads 2 \\
    --bind 127.0.0.1:5000 \\
    --timeout 60 \\
    --access-logfile /var/log/flask-app/access.log \\
    --error-logfile /var/log/flask-app/error.log \\
    wsgi:app
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF

# 10. Habilitar y iniciar servicio
echo "10. Iniciando servicio..."
sudo systemctl daemon-reload
sudo systemctl enable flask-app
sudo systemctl restart flask-app

# 11. Verificar estado
echo ""
echo "======================================"
echo "✅ Despliegue completado"
echo "======================================"
echo ""
echo "Estado del servicio:"
sudo systemctl status flask-app --no-pager
echo ""
echo "Logs (últimas líneas):"
sudo tail -10 /var/log/flask-app/error.log
echo ""
echo "Accede a: http://tu-ip-publica"
echo ""
