import socket 


servidor_remoto = "www.nic.br" #Endereço para ser verificado

def acesso_internet():
  try:
    host = socket.gethostbyname(servidor_remoto)
    s = socket.create_connection((host, 80), 2)
    return True
  except:
     pass
  return False




