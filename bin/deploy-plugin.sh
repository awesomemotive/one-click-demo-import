#!/bin/bash -ex

# Remote SVN repo on WordPress.org for this plugin
SVNURL="http://plugins.svn.wordpress.org/$PLUGINSLUG"
MAINFILE="$PLUGINSLUG.php"

# Let's begin...
echo ".........................................."
echo
echo "Preparing to deploy WordPress plugin"
echo
echo ".........................................."
echo

# Check version in readme.txt is the same as plugin file
READMEVERSION=`grep "^Stable tag" readme.txt | awk -F' ' '{print $3}'`
echo "readme version: $READMEVERSION"
PLUGINVERSION=`grep "^Version" $MAINFILE | awk -F' ' '{print $2}'`
echo "$MAINFILE version: $PLUGINVERSION"

if [ "$READMEVERSION" != "$PLUGINVERSION" ]; then
	echo "Versions don't match. Exiting...."
	exit 1
fi

echo "Versions match in readme.txt and PHP file. Let's proceed..."

echo
echo "Creating local copy of SVN repo ..."
svn co $SVNURL svn

cd svn

# Remove existing folders of this version
echo "Removing existing version folder..."
rm -rf trunk tags/$PLUGINVERSION

# Copy fresh code to trunk and tags folder
echo "Copying freshly pushed code to trunk and tags folder..."
mkdir trunk
mv ../$PLUGINSLUG/* trunk/
cp -r trunk tags/$PLUGINVERSION

# Add all new files to svn repo
svn add --force * --auto-props --parents --depth infinity -q
svn commit --no-auth-cache --username=$SVNUSERNAME --password=$SVNPASSWORD -m "Tagging version: $PLUGINVERSION"