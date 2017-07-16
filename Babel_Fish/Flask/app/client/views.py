from app import app                                                                         #import padrão da instancia Flask
#---------------------Import----------------------------------------------------------------#
from flask import render_template                                                           #Basicamente transforma HTML em uma string
from flask import redirect                                                                  #Usado para redirecionar para páginas desejadas
from flask import url_for                                                                   #Utilizado para pegar app.route da função desejada
from flask import jsonify                                                                   #Envio de resposta assíncrona para client-side
from flask import request                                                                   #
#-------------------------------------------------------------------------------------------#
       


#---------------------Import de Funções-----------------------------------------------------#
from app.client.components.helper import get_parameters                                     #Função para buscar parâmetros do arquivo config.json
from app.client.interface.basic_interface.interface import login, teste 
#-------------------------------------------------------------------------------------------#



#---------------------Import de Classes-----------------------------------------------------#
from app.client.form import Login, Basic_interface, Manutencao_parada
from app.client.interface.basic_interface.interface import Pedidos
#-------------------------------------------------------------------------------------------#



#\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\#
#/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/#
#\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\#
#/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/#
#\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\#



#---------------------Index - LOGIN---------------------------------------------------------#
@app.route('/', methods=['GET','POST'])
def index():
    form = Login()
 
    if form.validate_on_submit():
        login_response = login(form.username.data, form.password.data)
        if login_response:
            return redirect(url_for('basic_interface'))
            
        
    return render_template('login.html', form = form) 
#-------------------------------------------------------------------------------------------#


#---------------------BASIC INTERFACE-------------------------------------------------------#
@app.route('/basic_interface', methods=['GET','POST'])                                      #Roteamento 
def basic_interface():
    #Temporário
    classe = Pedidos('123', '456', '789', '2231') 
    dv = [123245334234, 21353445656, 'TIRA FQ TUBO SMTH', 0, 2324354634, 0, 183218, 10, 'METALSA CA', '00/00/00', 'Descrição', 6565, 432546546, 1,443, 21, 'MTM 125 05'] #Variáveis teste
    dv2 = [20280, 20279, 'F05', 'T25', 1421, 'Pronto', 'METALSA CA', 'TUBO RET S460MC 60 X 100 X 4,00 FQ', 'Stringse', 6860, 0, '00/00/00-13:34', 736542, 0, 20, '21:09:43', 'P'] #Variáveis teste
    #Temporário

    parameters = get_parameters()

    form = Basic_interface()                                                                #Criação de uma instância da classe
    form2 = Manutencao_parada()

    if (form.btn.data == True):                                                             #Verifica se o botão de POST foi pressionado
        teste(form.btn.data)                                                                #Chama função fora do request handler
   
    if form2.send.data == True:
        print('Foiiii')
        print(form2.select.data)

    return render_template('basic_interface.html', 
                           dv = dv, 
                           dv2 = dv2, 
                           form = form, 
                           form2 = form2,
                           classe = classe,
                           parameters = parameters)                                         #Render de HTML localizado na pasta tramplates + Passagem de vars para HTML
#-------------------------------------------------------------------------------------------#



#---------------------BACKGROUND PROCESS----------------------------------------------------#
@app.route('/background_process')
def background_process():
    classe = Pedidos('123', '456', '789', '2231') 
    classe.pedido1 = 'ASDASDAS'
    response = classe.pedido1
    return jsonify(result = response)
    

@app.route('/background_process2')
def background_process2():
    classe = Pedidos('123', '456', '789', '2231')  
    classe.pedido2 = 'nge2'
    response = classe.pedido2
    return jsonify(result2 = response)


@app.route('/background_process3')
def background_process3():
    classe = Pedidos('123', '456', '789', '2231') 
    classe.pedido3 = 'change3'
    response = classe.pedido3
    return jsonify(result3 = response)
#-------------------------------------------------------------------------------------------#



#---------------------ROTAS PARA TESTE------------------------------------------------------#
@app.route('/algum_lugar/')
def algum_lugar():  
        return "----------------------"


@app.route('/header/')
def header():
    return render_template('header.html')
#-------------------------------------------------------------------------------------------#

