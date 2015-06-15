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
- TYPO3 => 6.2.12



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

Todo:
-----

Improve debug/test options for rss/html parsing/fetching