Contributing to ErrLock
=======================

:+1: First, thanks for taking the time to contribute! :+1:

We are glad you're reading this, because we need volunteer developers to help
this project come to fruition.

The following is a set of guidelines for contributing to ErrLock and its
packages, which are hosted in the
[ErrLock Organization](https://github.com/ErrLock "ErrLock on GitHub") on
GitHub. These are mostly guidelines, not rules. Use your best judgment, and feel
free to propose changes to this document in a pull request.

Table Of Contents
-----------------

*	[Code of Conduct](#code-of-conduct)
*	[How Can I Contribute?](#how-can-i-contribute)
	-	[Reporting Bugs](#reporting-bugs)
	-	[Suggesting Enhancements](#suggesting-enhancements)
	-	[Submiting new code](#submitting-new-code)
*	[Styleguides](#styleguides)
	-	[Git Commit Messages](#git-commit-messages)
	-	[Documentation Styleguide](#documentation-styleguide)

Code of Conduct
---------------

This project and everyone participating in it is governed by the
[ErrLock Code of Conduct](CODE_OF_CONDUCT.md "ErrLock Code of Conduct"). By
participating, you are expected to uphold this code. Please report unacceptable
behavior to [conduct@errlock.org](mailto:conduct@errlock.org).

How Can I Contribute?
---------------------

### Reporting Bugs

This section guides you through submitting a bug report. Following these
guidelines helps maintainers and the community understand your report, reproduce
the behavior, and find related reports.

Before creating bug reports, please check
[this list](#before-submitting-a-bug-report) as you might find out that you
don't need to create one. When you are creating a bug report, please
[include as many details as possible](#how-do-i-submit-a-good-bug-report). Fill
out [the required template](ISSUE_TEMPLATE.md), the information it asks for
helps us resolve issues faster.

> **Note:** If you find a **Closed** issue that seems like it is the same thing
that you're experiencing, open a new issue and include a link to the original
issue in the body of your new one.

#### Before Submitting A Bug Report

*	Determine [which repository the problem should be reported in][errlock-and-packages].
*	Perform a [cursory search](https://github.com/search?q=+is%3Aissue+user%3AErrLock-Admin)
	to see if the problem has already been reported. If it has **and the issue
	is still open**, add a comment to the existing issue instead of opening a
	new one.

#### How Do I Submit A (Good) Bug Report?

Bugs are tracked as [GitHub issues](https://guides.github.com/features/issues/).
After you've determined [which repository][errlock-and-packages] your bug is
related to, create an issue on that repository and provide the following
information by filling in [the template](ISSUE_TEMPLATE.md).

Explain the problem and include additional details to help maintainers reproduce
the problem:
*	**Use a clear and descriptive title** for the issue to identify the problem.
*	**Describe the exact steps which reproduce the problem** in as many details
	as possible. For example, start by explaining how you started the program,
	which command exactly you used in the terminal, or how you started the
	program otherwise. When listing steps, **don't just say what you did, but
	explain how you did it**.
*	**Provide specific examples to demonstrate the steps**.
*	**Describe the behavior you observed after following the steps** and point
	out what exactly is the problem with that behavior.
*	**Explain which behavior you expected to see instead and why.**
*	**If the problem wasn't triggered by a specific action**, describe what you
	were doing before the problem happened and share more information using the
	guidelines below.

Provide more context by answering these questions:
*	**Did the problem start happening recently** (e.g. after updating to a new
	version) or was this always a problem?
*	If the problem started happening recently, **can you reproduce the problem
	in an older version?** What's the most recent version in which the problem
	doesn't happen?
*	**Can you reliably reproduce the issue?** If not, provide details about how
	often the problem happens and under which conditions it normally happens.

Include details about your configuration and environment:
*	**Which version are you using?**
*	**What's the name and version of the OS you're using**?
*	**Are you running the package in a virtual machine?** If so, which VM
	software are you using and which operating systems and versions are used for
	the host and the guest?

### Suggesting Enhancements

This section guides you through submitting an enhancement suggestion, including
completely new features and minor improvements to existing functionality.
Following these guidelines helps maintainers and the community understand your
suggestion and find related suggestions.

Before creating enhancement suggestions, please check
[this list](#before-submitting-an-enhancement-suggestion) as you might find out
that you don't need to create one. When you are creating an enhancement
suggestion, please [include as many details as possible](#how-do-i-submit-a-good-enhancement-suggestion).
Fill in [the template](ISSUE_TEMPLATE.md), including the steps that you imagine
you would take if the feature you're requesting existed.

#### Before Submitting An Enhancement Suggestion

*	**Determine [which repository the enhancement should be suggested in](#errlock-and-packages).**
*	**Perform a [search](https://github.com/search?q=+is%3Aissue+user%3AErrLock-Admin)**
	to see if the enhancement has already been suggested. If it has, add a
	comment to the existing issue instead of opening a new one.

#### How Do I Submit A (Good) Enhancement Suggestion?

Enhancement suggestions are tracked as
[GitHub issues](https://guides.github.com/features/issues/). After you've
determined [which repository][errlock-and-packages] your enhancement suggestion
is related to, create an issue on that repository and provide the following
information:
*	**Use a clear and descriptive title** for the issue to identify the
	suggestion.
*	**Provide a step-by-step description of the suggested enhancement** in as
	many details as possible.
*	**Provide specific examples to demonstrate the steps**.
*	**Describe the current behavior** and **explain which behavior you expected
	to see instead** and why.
*	**Explain why this enhancement would be useful** to most users.
*	**List some other applications where this enhancement exists.**
*	**Specify which version you're using.**
*	**Specify the name and version of the OS you're using.**

### Submiting new code

This section guides you through submitting new code. Following these guidelines
helps maintainers keeping the code clean and stable.

We follow the [GitHub WorkFlow](https://guides.github.com/introduction/flow/ "GitHub WorkFlow").

#### Create a new branch

*	Create your branch off of _master_.
*	Name your branch so that it is easily identified:
	-	**bugfix-**issue_title: For branches that aim to fix a bug.
	-	**feature-**feature_title: For branches that aim to implement a new
		feature.
	-	**other-**title: For anything else. Choose a descriptive title.

#### Write some code

*	Commit **often**
*	Document new code based on the [Documentation Styleguide](#documentation-styleguide)
*	Indent with four spaces
*	End all files with a newline
*	Avoid platform-dependent code
*	Place requires in the following order:
	1.	System packages
	2.	Local packages
*	Place class properties in the following order:
	1.	Constants
	2.	Static properties and methods
		1.	Properties
			1.	Public
			2.	Protected
			3.	Private
		2.	Methods
			1.	Public
			2.	Protected
			3.	Private
	3.	Instance properties and methods (same order as static ones)
*	This is open source software. Consider the people who will read your code,
	and make it look nice for them.

#### Test your code

*	Add/modify Tests relevant to your new code
*	Make sure **all** the tests pass with your new code

#### Create a Pull Request

*	Fill in [the required template](PULL_REQUEST_TEMPLATE.md)
*	Do not include issue numbers in the PR title

Styleguides
-----------

### Git Commit Messages

*	Use the present tense ("Add feature" not "Added feature")
*	Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
*	Limit the first line to 72 characters or less
*	Reference issues and pull requests liberally after the first line

### Documentation Styleguide

*	Use [Doxygen](http://www.doxygen.org/ "Doxygen") comments JavaDoc style
*	Document **every** file/class/constant/property/method

[errlock-and-packages]: https://github.com/ErrLock/ "ErrLock Repositories"
