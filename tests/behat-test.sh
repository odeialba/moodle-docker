#!/usr/bin/env bash
set -e

basedir="$( cd "$( dirname "${BASH_SOURCE[0]}" )/../" && pwd )"

export MOODLE_DOCKER_WWWROOT="${basedir}/moodle/lms"

if [ "$SUITE" = "behat" ];
then
    testcmd="bin/mbash mtest behat -d lms --tags=@auth_manual"
else
    echo "Error, unknown suite '$SUITE'"
    exit 1
fi

echo "Running: $testcmd"
$basedir/$testcmd
