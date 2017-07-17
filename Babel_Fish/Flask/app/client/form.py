from app import app
#----------------Formulários----------------------------------------------------------------#
from flask_wtf import FlaskForm                                                             #Import necessãrio para utilização do método POST
from wtforms import SubmitField                                                             #Classe para recebimento de bool
from wtforms import StringField                                                             #Classe para recebimento de string
from wtforms import PasswordField                                                           #Classe para recebimento de senhas
from wtforms import SelectField
from wtforms import validators                                                              #Validadores
#-------------------------------------------------------------------------------------------#


#----------------------Classes--------------------------------------------------------------#
class Login(FlaskForm):                                                                     #Criação da classe de formulários que voltarão em forma de POST 
    btn2 = SubmitField('Enviar')
    username = StringField('Endereço', [validators.DataRequired()])
    password = PasswordField('Senha', [validators.DataRequired()])


class Basic_interface(FlaskForm):
        btn = SubmitField('Inicia Produção')                                                #Indicação do tipo de campo do formulário
        

class Manutencao_parada(FlaskForm):
    select = SelectField('Motivo', choices=[('',''), ('pm','Desarme da Máquina'), ('pm','Desarme da Máquina'), ('pm','Desarme da Máquina')], default='')
    send = SubmitField('Enviar')
#-------------------------------------------------------------------------------------------#