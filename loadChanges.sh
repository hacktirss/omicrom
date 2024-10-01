#!/bin/sh

miCommit=$@
git status

printf "\n\n **** Recuerda cambiar la version...\n\n";
printf "Mi commit es:  $miCommit \n\n";

git add .

git commit -m "$miCommit"

git push origin desarrollo
