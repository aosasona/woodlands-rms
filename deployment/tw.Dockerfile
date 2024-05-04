FROM node:20-alpine

WORKDIR /app

COPY package.json package-lock.json tailwind.config.js ./

RUN npm install

COPY ./src/css/source.css ./in/source.css

CMD ["npm", "run", "watch"]
# CMD ["npm", "run", "tw"]
