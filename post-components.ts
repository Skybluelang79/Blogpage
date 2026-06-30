// src/app/post-list/post-list.component.ts
import { Component, OnInit } from '@angular/core';
import { ApiService } from '../api.service';

@Component({
  selector: 'app-post-list',
  templateUrl: './post-list.component.html'
})
export class PostListComponent implements OnInit {
  posts: any[] = [];

  constructor(private api: ApiService) {}

  ngOnInit() {
    this.api.getPosts().subscribe((data: any) => {
      this.posts = data;
    });
  }
}