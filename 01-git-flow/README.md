# \#1. Git Flow

## Some simple concepts

### What is git?

Git is a version control program that allows you to track changes in a set of files (generally a programming project, also known as a *repository* or just *repo*). It is distributed so it allows several people to collaboratively develop on the same project.

Git is simply a program in your computer, there is no Internet in the way git works at the most fundamental level. Git contains your whole repository, including past versions, so you can test your project and find in which "version" (commit) the project stopped working properly and who caused to malfunction.

### What is GitHub/GitLab?

GitHub is a company that remotely hosts git repositories. GitHub is where git really meets the Internet, and it is also the simplest and most common way to make git distributed and collaborative. GitLab is essentially the same as GitHub, providing some extra features for institutions that wish to deploy their own remote git hosting servers.

GitHub hosts the full git repository, which is accessible using a special link (aka the *origin*). If you wish to collaborate you must copy that remote git repo to your own machine (i.e., *clone* the repo) to get a working copy, then make the changes. After making the changes, you must create a new commit with those changes (by *commiting*), and afterwards you have to send to GitHub your new commit (aka *push* the commit). Notice that you only communicate with GitHub's servers when cloning and pushing, because editing and commiting is handled locally by git.

### Tags

A tag is just a human-readable name for a particular commit. Similarly to a commit, it references a specific version of your project.

### Wiki

For each repository you create in GitHub, GitHub also creates a meta-repository, called the wiki. It is basically where you place information about the project that does not belong with the code. For instance, class/function documentation goes together with code, but tutorials/guides should go into the wiki. The wiki is also useful to store any other kind of information that is not required for the project to work; in the case of LBAW, the wiki is where you will place your progress reports, as well as information about website/database architecture, etc.

The wiki is actually a git repository itself, so you can also clone it to edit it locally; this is highly advised, because you can use your favorite IDE/editor instead of the online file editor.

### gitignore

`.gitignore` is a very common special file. It is a file where each line contains a pattern that potentially matches files in your repository. git uses the `.gitignore` file in your repository (if it exists) to ignore certain folders when creating a commit. This means that you will not be annoyed by git about any file that matches one of the patterns in `.gitignore`. This is specially useful because you should avoid placing binary files in your repo as much as possible, since it is considered bad practice for a number of reasons. Binary files include for instance `.exe` and SQL database files.

## Main subjects

- Branches
- Pull requests
- Git flow
- Issues
