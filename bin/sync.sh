#!/bin/bash
#rm -f /Users/$(whoami)/.ssh/known_hosts
#rm -rf /Users/$(whoami)/Library/Application\ Support/Unison/*
unison -prefer newer -ignore "Name .git" -ignore "Path var" -ignorearchives -repeat watch /Users/admin/PhpstormProjects/clp-wp-varnish-cache/ ssh://root@127.0.0.1//home/wordpress/htdocs/wordpress.clp/wp-content/plugins/clp-varnish-cache/ -ui text
