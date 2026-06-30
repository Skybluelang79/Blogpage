// src/app/api.service.ts
import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';

@Injectable({ providedIn: 'root' })
export class ApiService {
  private baseUrl = 'https://yourdomain.com/api';
  private token = localStorage.getItem('token');

  constructor(private http: HttpClient) {}

  private authHeaders() {
    return {
      headers: new HttpHeaders({
        Authorization: this.token || ''
      })
    };
  }

  login(email: string, password: string) {
    return this.http.post(`${this.baseUrl}/auth.php`, { email, password });
  }

  getPosts() {
    return this.http.get(`${this.baseUrl}/posts.php`);
  }

  createPost(title: string, content: string) {
    return this.http.post(`${this.baseUrl}/posts.php`, { title, content }, this.authHeaders());
  }

  addComment(post_id: number, comment: string) {
    return this.http.post(`${this.baseUrl}/comments.php`, { post_id, comment }, this.authHeaders());
  }
}