import axios from 'axios';

export const HTTP_API = axios.create({
  baseURL: 'http://localhost/api',
  headers: {
    Accept: 'application/ld+json'
  },
  withCredentials: true
});

export const HTTP = axios.create({
  baseURL: 'http://localhost',
  headers: {
    Accept: 'application/ld+json'
  },
  withCredentials: true
});
