import socket #comentário


def acesso_network():
	s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
	s.connect(("8.8.8.8", 80))
	resposta = (s.getsockname()[0])
	s.close()
	return resposta
	

def teste_1():
	return True




