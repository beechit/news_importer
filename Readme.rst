TYPO3 news importer (ext:news_importer)
=======================================

Import news feeds into your TYPO3 CMS


Current supported resources:
----------------------------
- RSS feeds (atom,rss2,etc)
- (X)HTML pages
- XML content


Features:
---------

- Supports POST request (for example to do a login request)
- Supports sub requests (use found url in first feed to fetch content/details)
- Can import images
- Only import items that contain a certain keyword
- Add default image when no image found in imported item
- CommandController to automatically import items
- Backend module to manual import items
- Notification by email when new items are imported (when automatically imported)


Status:
-------

The extension is still beta but it is used already in multiple live projects


Requirements:
-------------
- TYPO3 => 10.4

Getting started
---------------

After installation of the extension:

- Create a record "Import source" on a define page/storage. (Listview -> add -> Import source)
- Enter the url of the desired import feed
- Add a mapping for the items (see example mapping configurations)
- Add news storage pid were the imported news items will be saved
- Adjust/add other fields

After defining which items to import:

- Create a scheduler task
- Choose Extbase command controller
- Select Command controller command: NewsImporter ImportNews:run
- Adjust settings to your preferences (frequency etc)

NOTE:

- In the import source there is an additional setting for interval!
- Importing can also be invoked by cmdline with ./typo3cms importnews:run (typo3_console ext have to be installed)



Example mapping configurations:
-------------------------------

Import RSS Feed as external news items::

	items = item
	item {
		guid = guid
		title = title
		externalurl = link
		type {
			defaultValue = 2
		}
		bodytext = description
		datetime {
			selector = pubDate
			strtotime = 1
		}
		image {
			selector = enclosure
			attr = url
		}
	}

Import RSS feed with multiple images::

	items = item
	item {
		guid = guid
		title = title
		externalurl = link
		type {
			defaultValue = 2
		}
		bodytext = description
		datetime {
			selector = pubDate
			strtotime = 1
		}
		image {
			selector = enclosure
			attr = url
			multiple = 1
		}
	}


Import custom RSS feed with multiple related links::

	items = item
	item {
		guid = guid
		title = title
		externalurl = link
		type {
			defaultValue = 2
		}
		bodytext = description
		datetime {
			selector = pubDate
			strtotime = 1
		}
		image {
			selector = enclosure
			attr = url
		}
		related_links {
			selector = related_link
			multiple {
				# fetch attr href from each
				uri = href

				# alternative way of fetching attr value and using other mapping options
				title {
					attr = title
					wrap = See also: |
				}
			}
		}
	}

Setting a default value can done by e.g. default author::

	author.defaultvalue = Authorname

Todo:
-----

Improve debug/test options for rss/html parsing/fetching


Upgrade to TYPO3 v10
--------------------

Migrating the Extbase command controllers to symfony commands:

importnews:status => newsimporter:outputnewsimportstatuses
importnews:testSource => newsimporter:testimportsource
importnews:run => newsimporter:importnews

!!! Removal of `be.editLink` Viewhelper, migrating to the core `be:uri.editRecord` Viewhelper

!!! Changing method ImportService->alreadyImported to always return true/false as the method name and codeblock suggests. An different
method ImportService->getNewsItemUid($pid,$guid) is created to get the newsItemUid of throws exception when no item is found.
