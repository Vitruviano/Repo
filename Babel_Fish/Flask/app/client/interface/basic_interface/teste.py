
from flask import render_template # Basicamente transforma HTML em uma string
from app import app #import padrão da instancia Flask
from flask_wtf import FlaskForm #Import necessãrio para utilização do método POST
from wtforms import SubmitField #Import do tipo de POST que será feito


dv = [123245334234, 21353445656, 'TIRA FQ TUBO SMTH', 0, 2324354634, 0, 183218, 10, 'METALSA CA', '00/00/00', 'Descrição', 6565, 432546546, 1,443, 21, 'MTM 125 05'] #Variáveis teste
dv2 = [20280, 20279, 'F05', 'T25', 1421, 'Pronto', 'METALSA CA', 'TUBO RET S460MC 60 X 100 X 4,00 FQ', 'Stringse', 6860, 0, '00/00/00-13:34', 736542, 0, 20, '21:09:43', 'P'] #Variáveis teste
   



class Btnform(FlaskForm): #Criação da classe de formulários que voltarão em forma de POST 
    btn = SubmitField('Inicia Produção') #Indicação do tipo de campo do formulário



@app.route('/', methods=['GET','POST']) #Roteamento 
def basic_interface():
    global dv, dv2

    form = Btnform() #Criação de uma instância da classe
    if (form.btn.data == True): #Verifica se o botão de POST foi pressionado ou não
        teste(form.btn.data) #Chama função fora do request handler
   
    return render_template('basic_interface.html', dv = dv, dv2 = dv2, form = form) #Render de HTML localizado na pasta tramplates + Passagem de vars para HTML


def teste(var): #Criação da função que será chamada pelo POST do usuário 
   
    print(str(var))
    



