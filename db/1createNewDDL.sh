java -cp ./FileGenerator-1.0.0.jar dolis.filegenerator.Main -d ./sql/ -postfix DDL -t sql
# Mac
cd sql
for file in \\*.sql;
do
   mv "$file" "${file#\\}"
done
