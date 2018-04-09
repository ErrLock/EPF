SRCS = $(shell find src docs/examples conf/doxygen -type f)

.PHONY: all clean force maint-clean doc gh-pages tests

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
	cd docs/html && git add . && git commit -m "Update gh-pages" && git push origin gh-pages
	date "+%s" > $@

gh-pages: gh-pages.time

tests:
	phpunit --include-path src/ tests/
