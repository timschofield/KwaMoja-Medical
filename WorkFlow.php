<p>This is a tutorial on creating a pull request on github using the <a class="doclink" href="http://datasift.github.com/gitflow/" target="_blank">hubflow tools</a>. It is largely based on the documentation provided by Data Sift on that link.</p>

<h2>1. Cloning A Repo</h2>

<p>Clone the existing repo from GitHub to your local workstation:</p>

<div class="code">git clone git@github.com:KwaMoja/KwaMoja</div>

<div class="info"><img class="icon" src="images/info.png" style="float: left;width:16px;" />Please remember:

    Do not fork the repo on GitHub - clone the master repo directly.</div>

<h2>2. Initialise The HubFlow Tools</h2>

<p>The HubFlow tools need to be initialised before they can be used:</p>

<div class="code">cd KwaMoja<br />
git hf init</div>

<div class="info"><img class="icon" src="images/info.png" style="float: left;width:16px;" />Please remember:

    You have to do this every time you clone a repo.</div>

<h2>3. Create A Feature Branch</h2>

<p>If you are creating a new feature branch, do this:</p>

<div class="code">git hf feature start KwaMoja</div>

<p>If you are starting to work on an existing feature branch, do this:</p>

<div class="code">git hf feature checkout KwaMoja</div>

<div class="info"><img class="icon" src="images/info.png" style="float: left;width:16px;" />Please remember:

    All new work (new features, non-emergency bug fixes) must be done in a new feature branch.<br />
    Give your feature branches sensible names. <br />
    If you’re working on a ticket, use the ticket number as the feature branch name (e.g. ticket-1234).<br />
    If the feature branch already exists on the master repo, this command will fail with an error.</div>

<h2>4. Publish The Feature Branch On GitHub</h2>

<p>Push your feature branch back to GitHub as you make progress on your changes:</p>

<div class="code">git hf feature push [##feature-name##]</div>

<h2>5. Keep Up To Date</h2>

<p>You’ll need to bring down completed features & hotfixes from other developers, and merge them into your feature branch regularly. (Once a day, first thing in the morning, is a good rule of thumb).</p>

<div class="code"># if you're not on your feature branch<br />
git hf feature checkout ##feature-name##<br />

# pull down master and develop branches<br />
git hf update<br />

# merge develop into your feature branch<br />
git merge develop</div>

<h2>6. Collaborate With Others</h2>

<p>Push your feature branch back to GitHub whenever you need to share your changes with colleagues:</p>

<div class="code">git hf feature push<br />

Pull your colleague’s changes back to your local clone:<br />

git hf feature pull</div>

<h2>7. Merge Your Feature Into Develop Branch</h2>

git hf feature push

Then, use the GitHub website to create a pull request to ##reponame##/develop branch from ##reponame##/feature/##feature-name##.

Pull Request

Pull Request

Pull Request

Pull Request

Pull Request

Ask a colleague to review your pull-request; don’t accept it yourself unless you have to. Once the pull request has been accepted, close your feature using the HubFlow tools:

git hf feature finish

