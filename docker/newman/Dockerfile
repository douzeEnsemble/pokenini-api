FROM node:alpine

RUN npm install -g newman newman-reporter-htmlextra

WORKDIR /etc/newman

# Set newman as the default container command
ENTRYPOINT ["newman"]
