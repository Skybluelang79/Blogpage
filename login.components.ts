// src/app/login/login.component.ts
import { Component } from '@angular/core';
import { ApiService } from '../api.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html'
})
export class LoginComponent {
  email = '';
  password = '';
  error = '';

  constructor(private api: ApiService, private router: Router) {}

  login() {
    this.api.login(this.email, this.password).subscribe((res: any) => {
      if (res.success) {
        localStorage.setItem('token', res.token);
        this.router.navigate(['/posts']);
      } else {
        this.error = res.message;
      }
    });
  }
}