FROM nginx:alpine

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

COPY public /var/www/html/public

WORKDIR /var/www/html

EXPOSE 80
