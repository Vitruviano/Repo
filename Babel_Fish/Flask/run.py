from flask_script import Manager #Biblioteca para gerenciamento do dev server do flask (--host, --debug)
from app import app 

<<<<<<< HEAD
manager = Manager(app)
app.secret_key = 'E15342GcbaFd' #Chave necessária para uso da biblioteca Flask Forms 

if __name__ == "__main__": #Verificar se o script específico está sendo executado 
	manager.run() #Executa 
=======
if __name__ == "__main__":
	app.run()

>>>>>>> adbd653682858cf456de12f90ce293979bcfddf9
