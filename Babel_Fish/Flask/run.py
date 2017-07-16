from app import app
#-------------------------------------------------------------------------------------------#
from flask_script import Manager                                                            #Biblioteca para gerenciamento do dev server do flask (--host, --debug)
#-------------------------------------------------------------------------------------------#



manager = Manager(app)
app.secret_key = 'E15342GcbaFd'                                                             #Chave necessária para uso da biblioteca Flask Forms 

if __name__ == "__main__":                                                                  #Verificar se o script específico está sendo executado 
	manager.run()                                                                           #Executa 