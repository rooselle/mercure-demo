import axios from 'axios';

export const HTTP_API = axios.create({
  baseURL: 'http://chloe-briquet.docker.eolas.lan/api',
  headers: {
    Accept: 'application/ld+json'
  },
  withCredentials: true
});

export const HTTP = axios.create({
  baseURL: 'http://chloe-briquet.docker.eolas.lan',
  headers: {
    Accept: 'application/ld+json'
  },
  withCredentials: true
});
