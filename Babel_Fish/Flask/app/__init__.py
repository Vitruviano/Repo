from flask import render_template, url_for # Basicamente transforma HTML em uma string
from flask import Flask
#--------------------------------------#

#--------------------------------------#
app = Flask(__name__)
#--------------------------------------#
from app.client.run.standard_process import start #chamada de aplicação 
from app.client.interface.basic_interface import teste #chamada de aplicação 
#--------------------------------------#





@app.route('/algum_lugar/')
def algum_lugar():  
        return "----------------------"



