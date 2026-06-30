// src/app/post-editor/post-editor.component.ts
import { Component } from '@angular/core';
import { ApiService } from '../api.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-post-editor',
  templateUrl: './post-editor.component.html'
})
export class PostEditorComponent {
  title = '';
  content = '';

  constructor(private api: ApiService, private router: Router) {}

  submit() {
    this.api.createPost(this.title, this.content).subscribe((res: any) => {
      if (res.success) {
        this.router.navigate(['/posts']);
      }
    });
  }
}