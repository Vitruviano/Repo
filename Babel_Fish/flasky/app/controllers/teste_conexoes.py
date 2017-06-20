from app import app #import padrão da instancia Flask
from app.controllers.pasta_teste_conexoes.acesso_internet   import acesso_internet #Chama a função
from app.controllers.acesso_network    import acesso_network #Chama a função
from app.controllers.acesso_impressora import acesso_impressora 

@app.route('/test/<int:id>') #Define a rota
def teste_conexoes(id):
	if id:
		status_conexao_internet = acesso_internet()
		status_conexao_network = acesso_network()
		status_conexao_impressora = acesso_impressora()
		return ('Acesso à Internet: '     + str(status_conexao_internet) +
			 '	|| Acesso à Network: '    + str(status_conexao_network)  +
			 '  || Acesso à Impressora: ' + str(status_conexao_impressora))
