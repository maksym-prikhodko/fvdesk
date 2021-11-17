Installing the Library
======================
Installing with Composer
------------------------
The recommended way to install Swiftmailer is via Composer:
nstalling from Git
-------------------
It's possible to download and install Swift Mailer directly from github.com if
you want to keep up-to-date with ease.
Swift Mailer's source code is kept in a git repository at github.com so you
can get the source directly from the repository.
loning the Repository
~~~~~~~~~~~~~~~~~~~~~~
The repository can be cloned from git://github.com/swiftmailer/swiftmailer.git
using the ``git clone`` command.
You will need to have ``git`` installed before you can use the
``git clone`` command.
To clone the repository:
* Open your favorite terminal environment (command line).
* Move to the directory you want to clone to.
* Run the command ``git clone git://github.com/swiftmailer/swiftmailer.git
  swiftmailer``.
The source code will be downloaded into a directory called "swiftmailer".
The example shows the process on a UNIX-like system such as Linux, BSD or Mac
OS X.
roubleshooting
---------------
Swift Mailer does not work when used with function overloading as implemented
by ``mbstring`` (``mbstring.func_overload`` set to ``2``). A workaround is to
temporarily change the internal encoding to ``ASCII`` when sending an email:
