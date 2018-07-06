#!/bin/bash
echo "======= WARNING =======";
echo;
echo "    PASSWORD CHANGER";
echo;
echo "======= WARNING =======";
echo;

newPass=$1
directory=$2
if [ -z "$newPass" ]; then
    echo "Error: Missing new password as first parameter.";
    echo "Command: $0 [new password] [directory]";
    exit 1;
fi
if [ -z "$directory" ]; then
    echo "Error: Missing applicable directory as second parameter.";
    echo "Command: $0 [new password] [directory]";
    exit 1;
fi

echo "Changing all strings 'THE_PASSWORD' to $newPass"
echo "In directory $directory"

read -p "Are you sure? (y/n) " -n 1 -r
echo;
if [[ $REPLY =~ ^[Yy]$ ]]
then
    pushd $directory > /dev/null
    echo "Updating passwords...";
    grep -rl THE_PASSWORD . | xargs sed -i s/THE_PASSWORD/$newPass/g
    popd > /dev/null
    echo "Done.";
fi
