
from flask import render_template # Basicamente transforma HTML em uma string
from app import app #import padrão da instancia Flask


@app.route('/') # Define a rota
def basic_interface():
    dv = [123245334234, 21353445656, 'TIRA FQ TUBO SMTH', 0, 2324354634, 0, 183218, 10, 'METALSA CA', '00/00/00', 'Descrição', 6565, 432546546, 1,443, 21, 32]
    return render_template('basic_interface.html', dv = dv) # Sem caminho necessário (Templates)




