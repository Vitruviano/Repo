from flask import Flask
from app import app
from app.__init__ import get_parameters
import socket
import subprocess



def network_access(parameters):
    s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    s.connect(("8.8.8.8", 80))
    response = (s.getsockname()[0])
    s.close()
    response_s = str(response)
    if response[:10] == (parameters['network_address']):
        return 'OK'
    else:
        return 'ERROR'

def ethernet_access(parameters):
    TCP_IP       =   parameters['tcp_ip']
    TCP_PORT     =   parameters['tcp_port']      
    BUFFER_SIZE  =   parameters['buffer_size'] 
    CONN_TIMEOUT =   parameters['connection_timeout']
 
    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    s.bind((TCP_IP, TCP_PORT))
    try:
        s.settimeout(CONN_TIMEOUT)
        s.listen(1)
        conn, addr = s.accept()

        while True:
            data = conn.recv(BUFFER_SIZE)
            if data: break

        conn.close()
        response = 'OK'
        return response
    except:
        print('timeout')
        response = 'FAILED'
        return response

def printer_access(parameters):
    address = str(parameters["printer_address"]) #Endereço a ser pingado
    res = subprocess.call(['ping',address]) #(['ping -c 1 ',address]) para Linux
    if res == 0:
	    return "OK: ip: %s" % address
    else:
	    return "No Response: ip: %s" % address



@app.route('/ct/')
def connection_teste():

    parameters = get_parameters()
    check_network_access = network_access(parameters)
    check_ethernet_access = ethernet_access(parameters)
    check_printer_access = printer_access(parameters) 
    
    return  (check_ethernet_access + '      ' + check_network_access + '       ' + check_printer_access)



