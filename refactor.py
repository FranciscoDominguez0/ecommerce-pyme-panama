import re
import os

path = r"C:\Users\Proyectos\ecommerce-pyme-panama\app\Http\Controllers\Admin\ProductoController.php"

with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Eliminar docblocks que no dicen nada interesante
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Muestra el listado de productos.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Genera un reporte PDF de Valorización.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Genera un reporte Excel de Valorización.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Muestra el formulario para crear.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Almacena un nuevo producto.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Muestra el formulario para editar.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Centraliza las consultas.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Actualiza un producto existente.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Elimina suavemente un producto.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Guarda imágenes enviadas.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Guarda imágenes subidas como archivos.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Procesa y guarda las variantes.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Calcula el stock total.*?\n\s*\*\/\s*\n', '', content)

# Remove simple comments
content = re.sub(r'\s*\/\/ Métricas KPI\n', '\n', content)
content = re.sub(r'\s*\/\/ Categorías para el filtro\n', '\n', content)
content = re.sub(r'\s*\/\/ Consulta base con eager loading\n', '\n', content)
content = re.sub(r'\s*\/\/ Obtener todos los productos activos.*?\n', '\n', content)
content = re.sub(r'\s*\/\/ Descarga el PDF.*?\n', '\n', content)
content = re.sub(r'\s*\/\/ Imágenes por URL\n', '\n', content)
content = re.sub(r'\s*\/\/ Imágenes por archivo\n', '\n', content)
content = re.sub(r'\s*\/\/ Asegurar al menos una imagen principal si existen imágenes\n', '\n', content)
content = re.sub(r'\s*\/\/ Guardar variantes si aplica\n', '\n', content)
content = re.sub(r'\s*\/\/ Eliminar imágenes marcadas para borrar\n', '\n', content)
content = re.sub(r'\s*\/\/ Actualizar el orden de las imágenes existentes.*?\n', '\n', content)
content = re.sub(r'\s*\/\/ Actualizar imagen principal si se seleccionó una existente\n', '\n', content)
content = re.sub(r'\s*\/\/ Agregar nuevas imágenes por URL\n', '\n', content)
content = re.sub(r'\s*\/\/ Agregar nuevas imágenes por archivo\n', '\n', content)
content = re.sub(r'\s*\/\/ Asegurar que al menos una imagen sea principal.*?\n', '\n', content)
content = re.sub(r'\s*\/\/ 1. Array de URLs desde las tarjetas.*?\n', '\n', content)
content = re.sub(r'\s*\/\/ 2. Campo de texto directo.*?\n', '\n', content)
content = re.sub(r'\s*\/\/ Eliminar variantes existentes si se desactivó\n', '\n', content)
content = re.sub(r'\s*\/\/ Estrategia simple: limpiar y recrear las variantes\n', '\n', content)
content = re.sub(r'\s*\/\/ ─── Helpers Privados ─────────────────────────────────────────────────────\n', '\n', content)


# Save back
with open(path, 'w', encoding='utf-8') as f:
    f.write(content)
