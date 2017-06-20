#Verificar conexão com a impressora
#Verificar funcionamento da impressora (papel, tinta)? 
#O codigo abaixo varre ips pingando cada um
#futuramente ele vai verificar somente um valor forncecido pela configuração 
#recebida pelo repositorio
import subprocess

def acesso_impressora():
    endereco = '10.200.6.157' #Endereço a ser pingado
    res = subprocess.call(['ping',endereco])
    print (res)
    if res == 0:
	    return "OK : ip: %s" % endereco
    elif res == 2:
	    return "No Response : ip: %s" % endereco
    else:
        return "Failed : ip: %s" % endereco