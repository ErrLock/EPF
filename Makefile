SRCS = $(shell find src -type f)

.PHONY: all clean maint-clean doc gh-pages tests

all:

clean:

maint-clean: clean
	rm -f srcs.list
	rm -f gh-pages.time

srcs.list: force
	@echo "$(SRCS)" | cmp -s - $@ || echo "$(SRCS)" > $@
	
docs/html: srcs.list $(SRCS)
	doxygen conf/doxygen/html.conf
	
doc: docs/html

gh-pages.time: docs/html
	cd docs/html && git push origin gh-pages
	date "+%s" > $@

gh-pages: gh-pages.time

tests:
	phpunit --include-path src/ tests/
