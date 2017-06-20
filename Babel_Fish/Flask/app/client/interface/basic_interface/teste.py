
from flask import render_template # Basicamente transforma HTML em uma string
from app import app #import padrão da instancia Flask

@app.route('/') # Define a rota
def basic_interface():
    return render_template('basic_interface.html') # Sem caminho necessário (Templates)


