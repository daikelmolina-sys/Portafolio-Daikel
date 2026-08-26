FROM nginx:alpine

# Default PORT environment variable if not provided (e.g., in local docker)
ENV PORT=80

# Remove default nginx content
RUN rm -rf /usr/share/nginx/html/*

# Copy static app files
COPY index.html /usr/share/nginx/html/
COPY css/ /usr/share/nginx/html/css/
COPY js/ /usr/share/nginx/html/js/

# Copy nginx template for dynamic port injection
COPY default.conf.template /etc/nginx/templates/default.conf.template

# Expose port
EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
