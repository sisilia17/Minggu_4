<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{


    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(array('form', 'url', 'html'));
        $this->load->library(array('form_validation', 'session'));
        $this->load->model('m_crud');
    }

    public function index()
    {
        if ($this->session->userdata('status') == 'login') {
            redirect('home/article');
        }
        $this->load->view('home/login');
    }

    public function loginAction()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');

        $where = [
            'username'  => $username,
        ];

        $cekQuery = $this->m_crud->loginQuery($where)->row();

        if ($cekQuery) {

            $data = $cekQuery;

            $truePass       = password_verify($password, $data->password);
            $roleAccess     = $data->role;

            if ($truePass) {

                $sess_data['id']        = $data->id;
                $sess_data['username']  = $data->username;
                $sess_data['role']      = $data->role;
                $sess_data['status']    = "login";

                $this->session->set_userdata($sess_data);

                if ($roleAccess == 1002) {
                    // echo "user biasa";
                    redirect('home/article');
                    return 0;
                } else if ($roleAccess == 1001) {
                    // echo "admin";
                    redirect('home/article');
                    return 0;
                }
            } else {
                $this->session->set_flashdata("danger", "Password yang anda masukkan salah");
                redirect('home');
                return 0;
            }
            //return article
        } else {
            $this->session->set_flashdata("danger", "Akun tidak ditemukan");
            redirect('home');
            return 0;
        }
    }


    public function article()
    {
        if ($this->session->userdata("status") == "login") {

            $data['dataArticle'] = $this->m_crud->getArticle()->result();

            $this->load->view('home/dataArticle', $data);
        } else {
            redirect('home', 'refresh');
        }
    }

    public function tambahArticle()
    {
        if ($this->session->userdata("status") == "login") {
            $this->load->view('home/addArticle', array('error' => ' '));
        } else {
            redirect('home', 'refresh');
        }
    }

    public function tambahArticles()
    {
        $this->form_validation->set_rules('title', 'Title', 'required', array('required' => 'Judul wajib diisi'));
        $this->form_validation->set_rules('article', 'Article', 'required', array('required' => 'Artikel Wajib di Isi'));

        if ($this->form_validation->run() != false) {

            $config['upload_path']            =    './upload   /';
            $config['allowed_types']        =    'gif|png|jpg';
            $config['max_size']                =    10000;

            $this->load->library('upload', $config);
            $this->upload->initialize($config);

            $title      = $this->input->post('title');
            $article    = $this->input->post('article');

            if (!$this->upload->do_upload('cover_img')) {
                $error = array('error' => $this->upload->display_errors());
                $this->load->view('home/addArticle', $error);
                return 0;
                $this->session->set_flashdata('error_upload_Images', 'gagal upload Images');
            } else {

                $data = array('upload_data' => $this->upload->data());
                $name = $data['upload_data'];

                $data = array(
                    'user_id'   => $this->session->userdata('id'),
                    'title'     => $title,
                    'article'   => $article,
                    'cover_img' => $name['file_name'],
                );

                $this->m_crud->uploadArticle('tbl_article', $data);
                $this->session->set_flashdata('success', 'Artikel Berhasil ditambahkan');
                redirect('home/article', 'refresh');
                return 0;
            }
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('home', 'refresh');
    }

    public function deleteArticle($id)
    {
        $where = ['id' => $id];
        $this->m_crud->deleteArticle($where, "tbl_article");
        $this->session->set_flashdata('success', 'Berhasil Menghapus Artikel');

        redirect('home/article');
        return 0;
    }

    public function update($id)
    {
        $where = ['id' => $id];
        $data['article'] = $this->m_crud->getUpdate("tbl_article", $where)->result();
        $this->load->view('home/updateArticle', $data);
    }

    public function updates()
    {
        $this->form_validation->set_rules('title', 'Title', 'required', array('required' => 'Judul wajib diisi'));
        $this->form_validation->set_rules('article', 'Article', 'required', array('required' => 'Artikel Wajib di Isi'));

        if ($this->form_validation->run() != false) {

            $config['upload_path']            =    './upload   /';
            $config['allowed_types']        =    'gif|png|jpg';
            $config['max_size']                =    10000;

            $this->load->library('upload', $config);
            $this->upload->initialize($config);

            $title      = $this->input->post('title');
            $article    = $this->input->post('article');
            $id         = $this->input->post('id');

            $where = ['id' => $id];

            if (!$this->upload->do_upload('cover_img')) {

                $data = array(
                    'title'     => $title,
                    'article'   => $article,
                );

                $this->m_crud->updateArticle('tbl_article', $data, $where);
                $this->session->set_flashdata('success', 'Berhasil Memperbaharui Artikel');
                redirect('home/article', 'refresh');
            } else {

                $data = array('upload_data' => $this->upload->data());
                $name = $data['upload_data'];

                $data = array(
                    'title'     => $title,
                    'article'   => $article,
                    'cover_img' => $name['file_name'],
                );

                $this->m_crud->updateArticle('tbl_article', $data, $where);
                $this->session->set_flashdata('success', 'Berhasil Memperbaharui Artikel');
                redirect('home/article', 'refresh');
                return 0;
            }
        }
    }
}
