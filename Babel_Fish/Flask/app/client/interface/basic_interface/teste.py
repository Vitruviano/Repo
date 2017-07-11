from flask import render_template # Basicamente transforma HTML em uma string
from flask import jsonify, request
from app import app #import padrão da instancia Flask
from flask_wtf import FlaskForm #Import necessãrio para utilização do método POST
from wtforms import SubmitField, StringField, PasswordField #Import do tipo de POST que será feito
from wtforms.validators import DataRequired


dv = [123245334234, 21353445656, 'TIRA FQ TUBO SMTH', 0, 2324354634, 0, 183218, 10, 'METALSA CA', '00/00/00', 'Descrição', 6565, 432546546, 1,443, 21, 'MTM 125 05'] #Variáveis teste
dv2 = [20280, 20279, 'F05', 'T25', 1421, 'Pronto', 'METALSA CA', 'TUBO RET S460MC 60 X 100 X 4,00 FQ', 'Stringse', 6860, 0, '00/00/00-13:34', 736542, 0, 20, '21:09:43', 'P'] #Variáveis teste
   

class Pedidos():
    def __init__(self, pedido1, pedido2, pedido3, pedido4):
        self.pedido1 = pedido1
        self.pedido2 = pedido2
        self.pedido3 = pedido3
        self.pedido4 = pedido4

    def delete(self, pedido):
        if (pedido == self.pedido1):
            print("Pedido1")
            self.pedido1 = 'Null'
        if (pedido == self.pedido2):
            print("Pedido2")
            self.pedido2 = 'Null'
        if (pedido == self.pedido3):
            print("Pedido3")
            self.pedido3 = 'Null'
classe = Pedidos('123', '456', '789', '2231')


class Btnform(FlaskForm): #Criação da classe de formulários que voltarão em forma de POST 
    btn = SubmitField('Inicia Produção') #Indicação do tipo de campo do formulário
    btn2 = SubmitField('Enviear')
    username = StringField('Endereço', validators=[DataRequired])
    password = PasswordField('Senha', validators=[DataRequired])

@app.route('/login')
def login():
    form2 = Btnform()
    if (form2.btn.data == True):
        print('Legal')
    return render_template('login.html', form = form2)


@app.route('/', methods=['GET','POST']) #Roteamento 
def basic_interface():
    global dv, dv2, classe

    form = Btnform() #Criação de uma instância da classe
    if (form.btn.data == True): #Verifica se o botão de POST foi pressionado
        teste(form.btn.data) #Chama função fora do request handler
   
    return render_template('basic_interface.html', dv = dv, dv2 = dv2, form = form, classe = classe) #Render de HTML localizado na pasta tramplates + Passagem de vars para HTML


@app.route('/background_process')
def background_process():
    global classe
    classe.pedido1 = 'ASDASDAS'
    response = classe.pedido1
    return jsonify(result = response)
    


@app.route('/background_process2')
def background_process2():
    global classe  
    classe.pedido2 = 'nge2'
    response = classe.pedido2
    return jsonify(result2 = response)


@app.route('/background_process3')
def background_process3():
    global classe  
    classe.pedido3 = 'change3'
    response = classe.pedido3
    return jsonify(result3 = response)



def teste(var): #Criação da função que será chamada pelo POST do usuário 
   
    print(str(var))
    
classe = Pedidos('123ASD', '456', '789', '001')
