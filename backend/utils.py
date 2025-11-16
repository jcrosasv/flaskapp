"""
Funciones auxiliares para la aplicación
"""

import openpyxl
from openpyxl.utils import get_column_letter
import pandas as pd
from typing import List, Tuple

def validate_excel_file(filepath: str) -> bool:
    """
    Valida que el archivo sea un Excel válido
    
    Args:
        filepath: Ruta del archivo
        
    Returns:
        bool: True si es válido, False en caso contrario
    """
    try:
        workbook = openpyxl.load_workbook(filepath, read_only=True)
        workbook.close()
        return True
    except Exception as e:
        print(f"Error validando archivo: {e}")
        return False

def read_excel_file(filepath: str) -> Tuple[List[str], List[List]]:
    """
    Lee un archivo Excel y retorna encabezados y datos
    
    Args:
        filepath: Ruta del archivo
        
    Returns:
        Tuple con encabezados y lista de filas
    """
    try:
        df = pd.read_excel(filepath)
        columns = df.columns.tolist()
        data = df.values.tolist()
        return columns, data
    except Exception as e:
        print(f"Error leyendo archivo: {e}")
        return [], []

def normalize_search_text(text: str) -> str:
    """
    Normaliza texto para búsqueda
    
    Args:
        text: Texto a normalizar
        
    Returns:
        Texto normalizado
    """
    return str(text).lower().strip()

def search_in_excel_data(data: List[List], columns: List[str], search_text: str) -> List[List]:
    """
    Busca en datos del Excel por cualquier columna
    Requiere mínimo 3 caracteres y busca parcialmente (contiene)
    
    Args:
        data: Datos del Excel
        columns: Nombres de las columnas
        search_text: Texto a buscar (mínimo 3 caracteres)
        
    Returns:
        Lista de filas que coinciden
    """
    # Validar mínimo de caracteres
    search_normalized = normalize_search_text(search_text).strip()
    if len(search_normalized) < 3:
        return []
    
    results = []
    
    for row in data:
        for i, value in enumerate(row):
            # Búsqueda parcial: si el valor contiene el texto buscado
            if search_normalized in normalize_search_text(str(value)):
                results.append(row)
                break
    
    return results

def format_search_results(columns: List[str], results: List[List]) -> List[List]:
    """
    Formatea los resultados de búsqueda para retornar al cliente
    Detecta columna LINK y la convierte en "EVIDENCIA" para mostrar como enlace
    
    Args:
        columns: Nombres de las columnas
        results: Filas de resultados
        
    Returns:
        Lista con encabezados y datos formateados
    """
    if not results:
        return []
    
    # Encontrar índice de la columna LINK
    link_col_index = None
    for i, col in enumerate(columns):
        if col.upper() == 'LINK':
            link_col_index = i
            break
    
    # Agregar encabezados como primera fila
    formatted = [columns]
    
    # Agregar datos, reemplazando None con vacío
    for row in results:
        formatted_row = []
        for i, cell in enumerate(row):
            # Si es la columna LINK y tiene valor, guardar la URL
            if i == link_col_index and cell:
                # Formato especial para links: "EVIDENCIA|URL"
                formatted_row.append(f"EVIDENCIA|{str(cell)}")
            else:
                formatted_row.append(str(cell) if cell is not None else '')
        formatted.append(formatted_row)
    
    return formatted

def allowed_file(filename: str, allowed_extensions: set) -> bool:
    """
    Verifica si la extensión del archivo está permitida
    
    Args:
        filename: Nombre del archivo
        allowed_extensions: Conjunto de extensiones permitidas
        
    Returns:
        bool: True si está permitida
    """
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in allowed_extensions
