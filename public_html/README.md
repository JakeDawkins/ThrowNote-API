# ThrowNote

------------------------ Using Usercake V.2.0.2 ------------------------
Modifications made:
1 - removed site templates
2 - removed header.php loading
3 - added proper indents
4 - removed echo statements
5 - closed all unclosed divs

------------------------ Product ------------------------

[+] Product Naming:
	- MyFeed
	- Feedist
	- Notery (misspelling on purpose)
	- Notee (notee.com available)
	- [+] ThrowNote

Summary: Twitter-like note taking for a private audience (individual use)

Use cases:
	- personal journaling
	- reminder notes
	- catch-space for daily life and task management
	- read-it-later archive for articles

Important:
	- No emails/notifications
	- All voluntary. No reminders
	- NOT a collaborative workspace (like evernote/drive)

Features:
	- focuses on short-form notes
	- allows some option for a longer form "note"
	- hashtag searching for tagging topics
	- pinning of key notes
	- hashtag listing (like keywords)
		- when searching a hashtag, can filter by other hashtags
		- ex: #articles --> #tech
	- content storage
		- images
		- pdfs
	- editable notes?

[+] ------------------------ HOW NEW NOTES ARE PROCESSED ------------------------
1. user enters note
2. process note
	2a. new note object
	2b. add text to note object
	2c. identify tags
	2d. add tags to note obj
	2e. save note object to db

------------------------ HOW NOTES ARE RETRIEVED ------------------------
1. Request note
2. Add markup to notes
	2a. add link to tags (to filter by tag)
	2b. add link to any http:// text
