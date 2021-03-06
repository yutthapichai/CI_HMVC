<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Blog extends MX_Controller
{
  public function __construct()
  {
    parent::__construct();

    /* Additition code which you want to run automatically*/
    $this->output->set_header('Last-Modified:' . gmdate('D, d M Y H:i:s') . 'GMT');
    $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
    $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
    $this->output->set_header('Pragma: no-cache');

    $this->load->model('PostsModel');
    $this->load->helper('text');
  }

  public function add_post()
  {
    if($this->session->userdata('is_logged_in') == FALSE)
    {
      redirect('login');
    }else {
      $this->form_validation->set_rules('title', 'Title', 'trim|required|min_length[20]|max_length[150]');
      $this->form_validation->set_rules('body', 'Body', 'trim|required|min_length[20]|max_length[500]');
      $this->form_validation->set_rules('category', 'Category', 'trim|required');
      if($this->form_validation->run() === FALSE)
      {
        $data['title'] = 'Add Post | '. $this->session->userdata('firstname');
        $data['module'] = 'posts';
        $data['view_file'] = 'add_post_view';
        echo Modules::run('templates/default_layout',$data);
      }else {
        $title     = $this->input->post('title');
        $body = $this->input->post('body');
        $category_id  = $this->input->post('category');
        $poster_id  = $this->input->post('category');
        $postData = array(
          'title' => $title,
          'body' => $body,
          'category_id' => $category_id,
          'poster_id' => $this->session->userdata('user_id')
        );
        $data['insert'] = $this->PostsModel->save($postData);
        if(!empty($data['insert']))
        {
          $this->session->set_flashdata('Addpost', 'You have add post successfully');
          redirect('my_post');
        }else{
          echo "cannot add post";
        }
      }
    }
  }

  public function my_post()
  {
    if($this->session->userdata('is_logged_in') == FALSE)
    {
      redirect('login');
    }else {
      $poster_id = $this->session->userdata('user_id');
      $data['posts'] = $this->PostsModel->get_my_post($poster_id);
      $data['title'] = 'My Post | '. $this->session->userdata('firstname');
      $data['module'] = 'posts';
      $data['view_file'] = 'my_post_view';
      echo Modules::run('templates/default_layout',$data);
    }
  }

  public function view_post()
  {
    $post_id = $this->uri->segment(2);
    if(empty($post_id))
    {
      show_404();
    }else {
      $data['view_posts'] = $this->PostsModel->get_view_post($post_id);
      $data['title'] = 'My Post | '. $this->session->userdata('firstname');
      $data['module'] = 'posts';
      $data['view_file'] = 'view_post_view';
      echo Modules::run('templates/default_layout',$data);
    }
  }

  public function save_comment()
  {
    $this->form_validation->set_rules('comment_body', 'comment Body' , 'trim|required|min_length[20]|max_length[500]');
    if($this->form_validation->run() === FALSE)
    {
      $post_id = $this->input->post('post_id');
      $this->session->set_flashdata('CommentValidation', validation_errors());
      redirect('view_post/'.$post_id);
    }else {
      $post_id = $this->input->post('post_id');
      $comment_data = array(
        'post_id' => $post_id,
        'commenter_id' => $this->session->userdata('user_id'),
        'comment_body' => $this->input->post('comment_body')
      );

      $message = Modules::run('comments/postcomments/save_comment', $comment_data);

      if(!empty($message))
      {
        $this->session->set_flashdata('CommentAdded', 'Comment Added successfully');
        redirect('view_post/'.$post_id);
      }
      else
      {
        echo "cannot save a comment";
      }
    }
  }

  public function view_authors_posts()
  {
    $poster_id = $this->uri->segment(2);

    if(empty($poster_id))
    {
      show_404();
    }

    //check if author exists
    $error = Modules::run('users/public_access/author_check', $poster_id);
    if($error == 'error')
    {
      $data['error'] = 'This Author do not exists..!';
    }
    $data['posts'] = $this->PostsModel->get_my_post($poster_id);
    $data['title'] = 'My Autthors Post';
    $data['module'] = 'posts';
    $data['view_file'] = 'view_author_posts_view';

    echo Modules::run('templates/default_layout',$data);

  }

  public function latest_posts()
  {
    $data['posts'] = $this->PostsModel->get_latest_post();
    $data['title'] = 'Last Post';
    $data['module'] = 'posts';
    $data['view_file'] = 'latest_post_view';
    echo Modules::run('templates/default_layout',$data);
  }

  public function dash_posts()
  {
    $data['posts'] = $this->PostsModel->get_latest_post();
    $this->load->view('dash_posts_view', $data);
  }
}
