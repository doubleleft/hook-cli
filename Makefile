# This Makefile downloads and installs hook-cli dependencies

SHELL := /bin/bash
CURPATH := $(shell pwd -P)
export PATH=$(HOME)/bin:$(shell echo $$PATH)

default: install

install:
	# check dependencies
ifneq ($(shell which php > /dev/null 2>&1; echo $$?),0)
	$(error "Missing php-cli.")
endif

ifneq ($(shell which npm > /dev/null 2>&1 > /dev/null; echo $$?),0)
	$(error "Missing npm.")
endif

	# install composer if we don't have it already
ifneq ($(shell which composer > /dev/null 2>&1 || test -x $(HOME)/bin/composer; echo $$?),0)
	mkdir -p $(HOME)/bin
	curl -sS https://getcomposer.org/installer | php -d detect_unicode=Off -- --install-dir=$(HOME)/bin --filename=composer
	chmod +x $(HOME)/bin/composer
endif

	composer install --no-dev --prefer-dist

	# ./commandline
	mkdir -p $(HOME)/bin
	ln -sf "$(CURPATH)/bin/hook" "$(HOME)/bin/hook"
	chmod +x "$(CURPATH)/bin/hook" "$(HOME)/bin/hook"
	npm --prefix "$(CURPATH)/console" install "$(CURPATH)/console"

	# add bash_completion
ifneq ($(shell grep -qs "completions/hook.bash" $(HOME)/.{profile,bash{rc,_profile}}; echo $$?),0)
ifeq ($(shell test -f $(HOME)/.bash_profile),0)
	echo "source $(CURPATH)/completions/hook.bash" >> $(HOME)/.bash_profile
else
	echo "source $(CURPATH)/completions/hook.bash" >> $(HOME)/.profile
endif
endif

	# add ~/bin to user PATH
ifneq ($(shell grep -qs "\(~\|\$${\?HOME}\?\)/bin" $(HOME)/.{profile,bash{rc,_profile}}; echo $$?),0)
ifeq ($(shell test -f $(HOME)/.bash_profile),0)
	echo "export PATH=~/bin:\$$PATH" >> $(HOME)/.bash_profile
else
	echo "export PATH=~/bin:\$$PATH" >> $(HOME)/.profile
endif
endif

	@echo "Finished"
