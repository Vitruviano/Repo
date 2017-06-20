from flask import Flask

app = Flask(__name__)

from app.controllers import teste_conexoes #chamada de aplicação
