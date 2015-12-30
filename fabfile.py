import os

from fabric.api import run, sudo, env, cd, local, prefix, put, lcd, settings
from fabric.contrib.project import rsync_project
from fabric.contrib.files import exists, sed

server_dir = '/home/project/code-review-server'
tmp_dir = '/tmp/code-review-server' + str(os.getpid()) + '/'

def _set_user_dir():
	global server_dir
	with settings(warn_only=True):
		issue = run('id root').lower()

def _prepare_local_website(install='true'):
	local('mkdir -p %s' % tmp_dir)
	local('cp -rv application vendor system index.php %s' % tmp_dir)

def prepare_remote_dirs():
	_set_user_dir()
	if not exists(server_dir):
		sudo('mkdir -p %s' % server_dir)
		sudo('chmod -R 755 %s' % server_dir)
		sudo('chown %s %s' % ('root', server_dir))

def host_type():
    run('uname -s')

def deploy(install='false'):
	_prepare_local_website(install)
	prepare_remote_dirs()
	rsync_project(local_dir=tmp_dir,remote_dir=server_dir,delete=True)
