from flask import render_template, url_for # Basicamente transforma HTML em uma string
from flask import Flask
#--------------------------------------#
import json
import os
#--------------------------------------#
app = Flask(__name__)
#--------------------------------------#
from app.client.interface.basic_interface import teste #chamada de aplicação 
from app.client.run.standard_process import start #chamada de aplicação 
#--------------------------------------#





@app.route('/algum_lugar/')
def algum_lugar():  
        return "----------------------"


def get_parameters():
    
    filename = os.path.join(app.static_folder, 'config.json')
    with open(filename) as config_file:
        json_object = config_file
        json_readable = json.load(json_object) 
    return json_readable
    

