FROM node:10.20.1

# set working directory
WORKDIR /app

# Copy initialization file
COPY start-script /usr/bin/start-script
RUN chmod +x /usr/bin/start-script

CMD ["start-script"]
