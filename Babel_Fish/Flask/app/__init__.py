
from flask import Flask

app = Flask(__name__)

from app.client.interface.teste1 import teste #chamada de aplicação
