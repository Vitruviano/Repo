
from flask import render_template # Basicamente transforma HTML em uma string
from app import app #import padrão da instancia Flask


@app.route('/') # Define a rota
def basic_interface():
    dv = [123245334234, 21353445656, 'TIRA FQ TUBO SMTH', 0, 2324354634, 0, 183218, 10, 'METALSA CA', '00/00/00', 'Descrição', 6565, 432546546, 1,443, 21, 'MTM 125 05']
    dv2 = [20280, 20279, 'F05', 'T25', 1421, 'Pronto', 'METALSA CA', 'TUBO RET S460MC 60 X 100 X 4,00 FQ', 'Stringse', 6860, 0, '00/00/00-13:34', 736542, 0, 20, '21:09:43', 'P']
    return render_template('basic_interface.html', dv = dv, dv2 = dv2) # Sem caminho necessário (Templates)




