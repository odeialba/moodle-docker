#!/usr/bin/env bash
set -e

basedir="$( cd "$( dirname "${BASH_SOURCE[0]}" )/../" && pwd )"

export MOODLE_DOCKER_WWWROOT="${basedir}/moodle/lms"

if [ "$SUITE" = "app" ] || [ "$SUITE" = "app-development" ];
then
    testcmd="bin/moodle-docker-compose exec -T webserver mtest behat -d lms --tags=@app&&@mod_login"
else
    echo "Error, unknown suite '$SUITE'"
    exit 1
fi

echo "Running: $testcmd"
$basedir/$testcmd
