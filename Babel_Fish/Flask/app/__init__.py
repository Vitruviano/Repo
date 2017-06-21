
from flask import Flask


app = Flask(__name__)

from app.client.interface.basic_interface import teste #chamada de aplicação



@app.route('/algum_lugar/')
def algum_lugar():  
        return "Algum_Lugar Aqui"
