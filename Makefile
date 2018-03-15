SRCS = $(shell find src -type f)

all:

srcs.list: force
	@echo "$(SRCS)" | cmp -s - $@ || echo "$(SRCS)" > $@
	
docs/html: srcs.list $(SRCS)
	doxygen conf/doxygen/html.conf
	
doc: docs/html

gh-pages.time: docs/html
	cd docs/html && git push origin gh-pages
	date "+%s" > $@

gh-pages: gh-pages.time

.PHONY: force doc gh-pages
