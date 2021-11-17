Message Headers
===============
Sometimes you'll want to add your own headers to a message or modify/remove
headers that are already present. You work with the message's HeaderSet to do
this.
Header Basics
-------------
All MIME entities in Swift Mailer -- including the message itself --
store their headers in a single object called a HeaderSet. This HeaderSet is
retrieved with the ``getHeaders()`` method.
As mentioned in the previous chapter, everything that forms a part of a message
in Swift Mailer is a MIME entity that is represented by an instance of
``Swift_Mime_MimeEntity``. This includes -- most notably -- the message object
itself, attachments, MIME parts and embedded images. Each of these MIME entities
consists of a body and a set of headers that describe the body.
For all of the "standard" headers in these MIME entities, such as the
``Content-Type``, there are named methods for working with them, such as
``setContentType()`` and ``getContentType()``. This is because headers are a
moderately complex area of the library. Each header has a slightly different
required structure that it must meet in order to comply with the standards that
govern email (and that are checked by spam blockers etc).
You fetch the HeaderSet from a MIME entity like so:
he job of the HeaderSet is to contain and manage instances of Header objects.
Depending upon the MIME entity the HeaderSet came from, the contents of the
HeaderSet will be different, since an attachment for example has a different
set of headers to those in a message.
You can find out what the HeaderSet contains with a quick loop, dumping out
the names of the headers:
ou can also dump out the rendered HeaderSet by calling its ``toString()``
method:
here the complexity comes in is when you want to modify an existing header.
This complexity comes from the fact that each header can be of a slightly
different type (such as a Date header, or a header that contains email
addresses, or a header that has key-value parameters on it!). Each header in the
HeaderSet is an instance of ``Swift_Mime_Header``. They all have common
functionality, but knowing exactly what type of header you're working with will
allow you a little more control.
You can determine the type of header by comparing the return value of its
``getFieldType()`` method with the constants ``TYPE_TEXT``,
``TYPE_PARAMETERIZED``, ``TYPE_DATE``, ``TYPE_MAILBOX``, ``TYPE_ID`` and
``TYPE_PATH`` which are defined in ``Swift_Mime_Header``.
eaders can be removed from the set, modified within the set, or added to the
set.
The following sections show you how to work with the HeaderSet and explain the
details of each implementation of ``Swift_Mime_Header`` that may
exist within the HeaderSet.
Header Types
------------
Because all headers are modeled on different data (dates, addresses, text!)
there are different types of Header in Swift Mailer. Swift Mailer attempts to
categorize all possible MIME headers into more general groups, defined by a
small number of classes.
Text Headers
~~~~~~~~~~~~
Text headers are the simplest type of Header. They contain textual information
with no special information included within it -- for example the Subject
header in a message.
There's nothing particularly interesting about a text header, though it is
probably the one you'd opt to use if you need to add a custom header to a
message. It represents text just like you'd think it does. If the text
contains characters that are not permitted in a message header (such as new
lines, or non-ascii characters) then the header takes care of encoding the
text so that it can be used.
No header -- including text headers -- in Swift Mailer is vulnerable to
header-injection attacks. Swift Mailer breaks any attempt at header injection by
encoding the dangerous data into a non-dangerous form.
It's easy to add a new text header to a HeaderSet. You do this by calling the
HeaderSet's ``addTextHeader()`` method.
hanging the value of an existing text header is done by calling it's
``setValue()`` method.
hen output via ``toString()``, a text header produces something like the
following:
f the header contains any characters that are outside of the US-ASCII range
however, they will be encoded. This is nothing to be concerned about since
mail clients will decode them back.
arameterized Headers
~~~~~~~~~~~~~~~~~~~~~
Parameterized headers are text headers that contain key-value parameters
following the textual content. The Content-Type header of a message is a
parameterized header since it contains charset information after the content
type.
The parameterized header type is a special type of text header. It extends the
text header by allowing additional information to follow it. All of the methods
from text headers are available in addition to the methods described here.
Adding a parameterized header to a HeaderSet is done by using the
``addParameterizedHeader()`` method which takes a text value like
``addTextHeader()`` but it also accepts an associative array of
key-value parameters.
o change the text value of the header, call it's ``setValue()`` method just as
you do with text headers.
To change the parameters in the header, call the header's ``setParameters()``
method or the ``setParameter()`` method (note the pluralization).
hen output via ``toString()``, a parameterized header produces something like
the following:
f the header contains any characters that are outside of the US-ASCII range
however, they will be encoded, just like they are for text headers. This is
nothing to be concerned about since mail clients will decode them back.
Likewise, if the parameters contain any non-ascii characters they will be
encoded so that they can be transmitted safely.
ate Headers
~~~~~~~~~~~~
Date headers contains an RFC 2822 formatted date (i.e. what PHP's ``date('r')``
returns). They are used anywhere a date or time is needed to be presented as a
message header.
The data on which a date header is modeled is simply a UNIX timestamp such as
that returned by ``time()`` or ``strtotime()``.  The timestamp is used to create
a correctly structured RFC 2822 formatted date such as
``Tue, 17 Feb 2009 22:26:31 +1100``.
The obvious place this header type is used is in the ``Date:`` header of the
message itself.
It's easy to add a new date header to a HeaderSet.  You do this by calling
the HeaderSet's ``addDateHeader()`` method.
hanging the value of an existing date header is done by calling it's
``setTimestamp()`` method.
hen output via ``toString()``, a date header produces something like the
following:
ailbox (e-mail address) Headers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Mailbox headers contain one or more email addresses, possibly with
personalized names attached to them. The data on which they are modeled is
represented by an associative array of email addresses and names.
Mailbox headers are probably the most complex header type to understand in
Swift Mailer because they accept their input as an array which can take various
forms, as described in the previous chapter.
All of the headers that contain e-mail addresses in a message -- with the
exception of ``Return-Path:`` which has a stricter syntax -- use this header
type. That is, ``To:``, ``From:`` etc.
You add a new mailbox header to a HeaderSet by calling the HeaderSet's
``addMailboxHeader()`` method.
hanging the value of an existing mailbox header is done by calling it's
``setNameAddresses()`` method.
f you don't wish to concern yourself with the complicated accepted input
formats accepted by ``setNameAddresses()`` as described in the previous chapter
and you only want to set one or more addresses (not names) then you can just
use the ``setAddresses()`` method instead.
f all you want to do is set a single address in the header, you can use a
string as the input parameter to ``setAddresses()`` and/or
``setNameAddresses()``.
hen output via ``toString()``, a mailbox header produces something like the
following:
D Headers
~~~~~~~~~~
ID headers contain identifiers for the entity (or the message). The most
notable ID header is the Message-ID header on the message itself.
An ID that exists inside an ID header looks more-or-less less like an email
address.  For example, ``<1234955437.499becad62ec2@example.org>``.
The part to the left of the @ sign is usually unique, based on the current time
and some random factor. The part on the right is usually a domain name.
Any ID passed to the header's ``setId()`` method absolutely MUST conform to
this structure, otherwise you'll get an Exception thrown at you by Swift Mailer
(a ``Swift_RfcComplianceException``).  This is to ensure that the generated
email complies with relevant RFC documents and therefore is less likely to be
blocked as spam.
It's easy to add a new ID header to a HeaderSet.  You do this by calling
the HeaderSet's ``addIdHeader()`` method.
hanging the value of an existing date header is done by calling its
``setId()`` method.
hen output via ``toString()``, an ID header produces something like the
following:
ath Headers
~~~~~~~~~~~~
Path headers are like very-restricted mailbox headers. They contain a single
email address with no associated name. The Return-Path header of a message is
a path header.
You add a new path header to a HeaderSet by calling the HeaderSet's
``addPathHeader()`` method.
hanging the value of an existing path header is done by calling its
``setAddress()`` method.
hen output via ``toString()``, a path header produces something like the
following:
eader Operations
-----------------
Working with the headers in a message involves knowing how to use the methods
on the HeaderSet and on the individual Headers within the HeaderSet.
Adding new Headers
~~~~~~~~~~~~~~~~~~
New headers can be added to the HeaderSet by using one of the provided
``add..Header()`` methods.
To add a header to a MIME entity (such as the message):
Get the HeaderSet from the entity by via its ``getHeaders()`` method.
* Add the header to the HeaderSet by calling one of the ``add..Header()``
  methods.
The added header will appear in the message when it is sent.
etrieving Headers
~~~~~~~~~~~~~~~~~~
Headers are retrieved through the HeaderSet's ``get()`` and ``getAll()``
methods.
To get a header, or several headers from a MIME entity:
* Get the HeaderSet from the entity by via its ``getHeaders()`` method.
* Get the header(s) from the HeaderSet by calling either ``get()`` or
  ``getAll()``.
When using ``get()`` a single header is returned that matches the name (case
insensitive) that is passed to it. When using ``getAll()`` with a header name,
an array of headers with that name are returned. Calling ``getAll()`` with no
arguments returns an array of all headers present in the entity.
heck if a Header Exists
~~~~~~~~~~~~~~~~~~~~~~~~
You can check if a named header is present in a HeaderSet by calling its
``has()`` method.
To check if a header exists:
* Get the HeaderSet from the entity by via its ``getHeaders()`` method.
* Call the HeaderSet's ``has()`` method specifying the header you're looking
  for.
If the header exists, ``true`` will be returned or ``false`` if not.
emoving Headers
~~~~~~~~~~~~~~~~
Removing a Header from the HeaderSet is done by calling the HeaderSet's
``remove()`` or ``removeAll()`` methods.
To remove an existing header:
* Get the HeaderSet from the entity by via its ``getHeaders()`` method.
* Call the HeaderSet's ``remove()`` or ``removeAll()`` methods specifying the
  header you want to remove.
When calling ``remove()`` a single header will be removed. When calling
``removeAll()`` all headers with the given name will be removed. If no headers
exist with the given name, no errors will occur.
odifying a Header's Content
~~~~~~~~~~~~~~~~~~~~~~~~~~~~
To change a Header's content you should know what type of header it is and then
call it's appropriate setter method. All headers also have a
``setFieldBodyModel()`` method that accepts a mixed parameter and delegates to
the correct setter.
To modify an existing header:
* Get the HeaderSet from the entity by via its ``getHeaders()`` method.
* Get the Header by using the HeaderSet's ``get()``.
* Call the Header's appropriate setter method or call the header's
  ``setFieldBodyModel()`` method.
The header will be updated inside the HeaderSet and the changes will be seen
when the message is sent.
