
from app import app #import padrão da instancia Flask


@app.route('/') #Define a rota
def teste():
    return "Testando Estrutura de pastas e funcionamento Básico dentro do VS"