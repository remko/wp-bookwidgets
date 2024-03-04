VERSION ?= $(shell git describe)
RELEASE_FILES=wp-bookwidgets/readme.txt wp-bookwidgets/wp-bookwidgets.php wp-bookwidgets/wp-bookwidgets.css
RELEASE_PACKAGE=wp-bookwidgets_$(VERSION).zip

all: readme.txt

readme.txt: README.md plugin.txt
	(cat plugin.txt && cat README.md | sed -e 's/^# .*//' | sed -e 's/^## \(.*\)/== \1 ==/') > $@

check:
	COVERAGE=$(COVERAGE) php test.php


wp-bookwidgets/%: %
	[ -d "wp-bookwidgets" ] || mkdir -p wp-bookwidgets
	cp $< $@

.PHONY: release
release: $(RELEASE_FILES)
	zip -r $(RELEASE_PACKAGE) $(RELEASE_FILES)
	if [ -d svn ]; then cp $(RELEASE_FILES) svn/trunk; cp assets/* svn/assets; fi

.PHONY: clean
clean:
	-rm -rf $(RELEASE_PACKAGE) *.zip readme.txt

.PHONY: dev-wordpress
dev-wordpress:
	docker compose up
