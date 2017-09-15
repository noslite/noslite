FROM php:7.1-cli
COPY . /usr/src/noslite
WORKDIR /usr/src/noslite
CMD [ "php", "./src/noslite.php" ]
