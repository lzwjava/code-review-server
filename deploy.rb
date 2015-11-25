#!/usr/bin/env ruby

require 'FileUtils'
require 'net/http'

class Deployer
	
	def exit_with_info(info)
	  abort("\n#{info}\n")
	end

	def log(info)
	  info = "====== #{info} ======"
	  line = '=' * info.length
	  info = "\n#{line}\n#{info}\n#{line}\n"
	  puts info
	end

	def execute(cmd)
	  ok = system(cmd)

	  exit_with_info(">>> Error when execute command:\n>>> #{cmd}") unless ok
	end

	def deploy
		Dir.chdir 'web-project'
		execute('gulp build')
		Dir.chdir '../server'
		execute('avoscloud deploy')
		execute('avoscloud publish')
	end
end

Deployer.new.deploy();
