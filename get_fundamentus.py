# -*- coding: ISO-8859-1 -*-
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""
(c) 2013, 2014, GoGo40 - Pï¿½ricles Lopes Machado

Script que baixa os dados fundamentalista do site fundamentus.com.br 
e gera um arquivo xls com todos dados.
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""

from HTMLParser import HTMLParser
from subprocess import call
import pycurl
import cStringIO
import time
import string
import json
import re

######################################################################
"""
Funcoes utilitarias
"""

def is_number(s):
    try:
        float(s)
        return True
    except ValueError:
        return False

####################################################################
"""
Funcao que gera um arquivo a partir de um link
"""
use_wget = True
fn = "get_buffer"
duracao_pausa = 0.5

def get_file(link):
	global use_wget, fn

	print "Baixando " + link + "..."
	lista_f = ""
	
	if use_wget:
		call(["wget", link, "-O", fn, "-o", "log_fundamentus"])

		lista_f = ""

		with open(fn) as f:
			for l in f:
				lista_f = lista_f + l
	else:
		buf = cStringIO.StringIO()
	
		c = pycurl.Curl()
		c.setopt(c.URL, link)
		c.setopt(c.WRITEFUNCTION, buf.write)
		c.perform()
		
		lista_f = buf.getvalue()
		buf.close()
		
		return lista_f

	print link + " baixado! =)"
	
	time.sleep(duracao_pausa)
	
	return lista_f


####################################################################
"""
Definições
"""

link_lista_acoes = "www.fundamentus.com.br/detalhes.php"

link_base = "www.fundamentus.com.br/"


####################################################################

"""
Obtendo lista de papeis
"""

lista_f = get_file(link_lista_acoes)


#Armazena links e nomes
lista_links = []
lista_nomes = []

is_td = 0
is_a = 0

#Parser para processar lista de links

class LinkHTMLParser(HTMLParser):
	def handle_starttag(self, tag, attrs):
		global is_td, is_a, lista_links, lista_nomes
		
		if tag == 'td':
			is_td = is_td + 1
		elif tag == 'a' and is_td > 0:
			is_a = is_a + 1
			if len(attrs) > 0:
				for attr in attrs:
					if attr[0] == 'href':
						lista_links.append(link_base + attr[1])
		
		
	def handle_endtag(self, tag):
		global is_td, is_a, lista_links, lista_nomes

		if tag == 'a' and is_td > 0:
			is_a = is_a - 1
		elif tag == 'td':
			is_td = is_td - 1

	def handle_data(self, data):
		global is_td, is_a, lista_links, lista_nomes
		
		if is_a > 0:
			lista_nomes.append(data)

#Processa link
parser = LinkHTMLParser()
parser.feed(lista_f)

c = 0
#for link in lista_links:
#	nome = lista_nomes[c]
#	print nome, "->", link
#	c = c + 1


#Parser para processar os dados de cada aÃ§Ã£o

var_nomes = []
var_dados = []
is_td = 0
is_a = 0
is_name = 1

nomes_fora = {}

nomes_fora['Indicadores fundamentalistas'] = 1
nomes_fora['Oscilações'] = 1
nomes_fora['Dados Balanço Patrimonial'] = 1
nomes_fora['Dados demonstrativos de resultados'] = 1
nomes_fora['Últimos 12 meses'] = 1
nomes_fora['Últimos 3 meses'] = 1

class AcaoHTMLParser(HTMLParser):
	def handle_starttag(self, tag, attrs):
		global is_td, is_a, var_nomes, var_dados
		if tag == 'td':
			is_td = is_td + 1


	def handle_endtag(self, tag):
		global is_td, is_a, var_nomes, var_dados

		if tag == 'td':
			is_td = is_td - 1
		
	def handle_data(self, data):
		global is_td, is_a, var_nomes, var_dados, is_name, nomes_fora
		
		if is_td > 0:
			if data != '?' and not (data in nomes_fora.keys()):
				if is_name:
					var_nomes.append(data)
					is_name = 0
				else:
					data_n = ""
					c = 0
					for d in data.split():
						if c == 0:
							data_n = d
						else:
							data_n = d + " " + data
						c = c + 1
					
					var_dados.append(data_n)
					
					is_name = 1

#Processa link

dados = []

N = len(lista_nomes)
ord_nomes = []

raw_data = {}

for i in range(0, N): #len(lista_nomes)):
	print "PROCESSANDO LINK ", (i+1), "de", N
	print lista_nomes[i], lista_links[i]
	print "####################################"

	lista_f = get_file(lista_links[i])

	var_nomes = []
	var_dados = []
	is_td = 0
	is_a = 0
	is_name = 1

	parser = AcaoHTMLParser()
	parser.feed(lista_f)

	l_d = []
	
	info_stock = {}
	for k in range(0, len(var_nomes)):
		nome_ = nome = dado = "-"
		
		if k < len(var_nomes):
			nome = var_nomes[k]
			allow = "[^A-Za-z0-9. /\-]"
			print nome, allow
			nome_ = re.sub(allow, '', nome)
			
		if k < len(var_dados):
			dado = var_dados[k]
		
		print nome, "->", dado, " k = ", k
		
		if i == 0:
			ord_nomes.append(nome)
		
		if k < len(var_nomes) and k < len(var_dados):
			dado = dado.replace('.', '')
			dado = dado.replace(',', '.')
			dado = dado.replace('%', '')

			dado_ = '-'
			if is_number(dado):
				dado_ = float(dado)
			else:
				allow = "[^A-Za-z0-9. /\-]"
				print dado, allow
				dado_ = re.sub(allow, '', dado)
				
			info_stock[nome_] = dado_

		l_d.append(dado)
	
	raw_data[var_dados[0]] = info_stock

	dados.append(l_d)

file_name = "fundamentus.xls"


with open(file_name, 'w') as f:
	for k in ord_nomes:
		f.write(k + "\t")
	f.write("\n")
	
	for i in range(0, N):
		for d in dados[i]:
			f.write(d + "\t")
		f.write("\n")

#print raw_data

file_name = "fundamentus.json"
with open(file_name, 'w') as f:
	f.write(json.dumps(raw_data) + "\n")

