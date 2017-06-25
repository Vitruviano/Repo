from flask_script import Manager
from app import app

manager = Manager(app)
app.secret_key = 'someverycomplexkey'

@manager.command
def hello():
    print ("hello")

if __name__ == "__main__":
	manager.run()