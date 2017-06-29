from flask import render_template # Basicamente transforma HTML em uma string
from flask import Flask


app = Flask(__name__)

from app.client.interface.basic_interface import teste #chamada de aplicação asd asd



@app.route('/algum_lugar/')
def algum_lugar():  
        return "----------------------"

@app.route('/atualizar/')
def atualizar():
    return render_template('atualizar_interface.html')
