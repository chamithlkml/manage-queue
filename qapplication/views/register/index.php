<?php
/**
 * Created by Chamith Jayaweera
 * User: chamith
 * Date: 8/11/18
 * Time: 2:24 PM
 */
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="container">
    <br>
    <div class="row">
        <div class="col-md-3"></div>
        <div class="col-md-6">
            <form>
                <div class="form-group">
                    <label for="exampleInputEmail1">Email address</label>
                    <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter email">
                    <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Password</label>
                    <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="exampleCheck1">
                    <label class="form-check-label" for="exampleCheck1">Check me out</label>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
        <div class="col-md-3"></div>
    </div>
</div>