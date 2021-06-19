<?php
defined('BASEPATH') OR exit('No direct script access allowed');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");

use chriskacerguis\RestServer\RestController;
require(APPPATH .'libraries/fpdf/fpdf.php');

class Api extends RestController {
	function __construct() {
		parent::__construct();
		$this->load->model('users');
		$this->load->model('payments');
		$this->load->model('summary');
		$this->load->model('groups');
		$this->load->model('usersGroups');
		$this->load->model('admins');
	}

	public function users_get() {
		$users = $this->users->all();

		if ( $users ) {
			$this->response( $users, RestController::HTTP_OK );
		} else {
			$this->response( [ 'status' => false, 'message' => 'No se encontraron usuarios' ], RestController::HTTP_BAD_REQUEST );
		}
	}

	public function search_user_post() {
		$data = $this->post();

		$user = $this->users->search($data);
		$user->cursos = $this->usersGroups->allJoinGroupSummary(['ug.idUser' => $data['id']]);

		foreach ($user->cursos as $key => $value) {
			$value->pagos = $this->payments->all(['idSummary' => $value->idSummary, 'idGroup' => $value->idGroup, 'idUser' => $value->idUser]);
		}

		if ( $user ) {
			$this->response( $user, RestController::HTTP_OK );
		} else {
			$this->response( [ 'status' => false, 'message' => 'No users were found' ], RestController::HTTP_BAD_REQUEST );
		}
	}

	public function summary_get() {
		$summary = $this->summary->all();

		foreach ($summary as $key => $value) {
			$groups = $this->groups->all(['idSummary' => $value->id]);
			$value->grupos = $groups;
		}

		if ( $summary ) {
			$this->response( $summary, RestController::HTTP_OK );
		} else {
			$this->response( [ 'status' => false, 'message' => 'No existen cursos guardados.' ], RestController::HTTP_BAD_REQUEST );
		}
	}

	public function search_summary_post() {
		$data = $this->post();

		$summary = $this->summary->search($data);
		$groups = $this->groups->all(['idSummary' => $data['id']]);
		$summary->grupos = $groups;

		if ($groups) {
			foreach ($groups as $key => $value) {
				$value->alumnos = $this->usersGroups->all(['idGroup' => $value->id]);
			}
		}

		if ( $summary ) {
			$this->response( $summary, RestController::HTTP_OK );
		} else {
			$this->response( [ 'status' => false, 'message' => 'No users were found' ], RestController::HTTP_BAD_REQUEST );
		}
	}

	public function new_summary_post() {
		$data = $this->post();
		$data['alias'] = $this->_transform_alias($data['curso']);

		if ($this->summary->search(['alias' => $data['alias']])) {
			$this->response( [ 'status' => false, 'message' => 'El curso ya existe' ], RestController::HTTP_BAD_REQUEST );
		}else{
			$summary_id = $this->summary->insert($data);

			if ( $summary_id > 0 ) {
				$this->response( [ 'status' => true, 'message' => 'El curso se creo correctamente!' ], RestController::HTTP_OK );
			} else {
				$this->response( [ 'status' => false, 'message' => 'No users were found' ], RestController::HTTP_BAD_REQUEST );
			}
		}
	}

	public function new_group_post() {
		$data = $this->post();

		if ($this->groups->search(['grupo' => $data['grupo']])) {
			$this->response( [ 'status' => false, 'message' => 'El grupo ya existe, no se guardaron los datos.' ], RestController::HTTP_BAD_REQUEST );
		}else{
			$group_id = $this->groups->insert($data);

			if ( $group_id > 0 ) {
				$this->response( [ 'status' => true, 'message' => 'El grupo se creo correctamente!' ], RestController::HTTP_OK );
			} else {
				$this->response( [ 'status' => false, 'message' => 'No se pudo crear el grupo' ], RestController::HTTP_BAD_REQUEST );
			}
		}
	}

	public function new_user_post() {
		$data = $this->post();

		if ($this->users->search(['clave' => $data['clave']])) {
			$this->response( [ 'status' => false, 'message' => 'El alumno ya existe' ], RestController::HTTP_BAD_REQUEST );
		}else{
			$user_id = $this->users->insert($data);

			if ( $user_id > 0 ) {
				$this->response( [ 'status' => true, 'message' => 'El alumno se creo correctamente!' ], RestController::HTTP_OK );
			} else {
				$this->response( [ 'status' => false, 'message' => 'No se pudo crear el alumno' ], RestController::HTTP_BAD_REQUEST );
			}
		}
	}

	public function edit_summary_post() {
		$data = $this->post();
		$alias = $this->_transform_alias($data['curso']);
		$where = ['id' => $data['id']];
		$set = ['curso' => $data['curso'], 'siglas' => $data['siglas'], 'alias' => $alias, 'precio' => $data['precio']];
		$this->summary->update($where, $set);
		$this->response( [ 'status' => true, 'message' => 'Los datos del curso fueron editados' ], RestController::HTTP_OK );
	}

	public function groups_get() {
		$groups = $this->groups->allJoinSummary();

		foreach ($groups as $key => $value) {
			$value->alumnos = $this->usersGroups->all(['idGroup' => $value->id]);
		}

		if ( $groups ) {
			$this->response( $groups, RestController::HTTP_OK );
		} else {
			$this->response( [ 'status' => false, 'message' => 'No users were found' ], RestController::HTTP_BAD_REQUEST );
		}
	}

	public function groups_post() {
		$data = $this->post();
		$groups = $this->groups->all(['idSummary' => $data['id']]);

		if ( $groups ) {
			$this->response( $groups, RestController::HTTP_OK );
		} else {
			$this->response( [ 'status' => false, 'message' => 'No users were found' ], RestController::HTTP_BAD_REQUEST );
		}
	}

	public function search_groups_get() {
		$groups = $this->groups->all(['idSummary' => NULL	]);

		if ( $groups ) {
			$this->response( $groups, RestController::HTTP_OK );
		} else {
			$this->response( [ 'status' => false, 'message' => 'No hay grupos disponibles.' ], RestController::HTTP_BAD_REQUEST );
		}
	}

	public function edit_group_post() {
		$data = $this->post();
		$where = ['id' => $data['id']];
		if (!isset($data['idSummary'])) { $data['idSummary'] = NULL; }
		$set = ['idSummary' => $data['idSummary'], 'grupo' => $data['grupo'], 'fecha' => $data['fecha']];
		$this->groups->update($where, $set);
		$this->response( [ 'status' => true, 'message' => 'Los datos del grupo fueron editados' ], RestController::HTTP_OK );
	}

	public function edit_user_post() {
		$data = $this->post();
		$where = ['id' => $data['id']];
		$set = ['nombre' => $data['nombre'], 'apellido_paterno' => $data['apellido_paterno'], 'apellido_materno' => $data['apellido_materno'], 'clave' => $data['clave']];
		$this->users->update($where, $set);
		$this->response( [ 'status' => true, 'message' => 'Los datos del usuario fueron editados' ], RestController::HTTP_OK );
	}

	public function group_detail_post() {
		$data = $this->post();
		$group = $this->groups->searchJoinSummary(['g.id' => $data['id']]);
		$group->alumnos = $this->usersGroups->allJoinUser(['ug.idGroup' => $data['id']]);

		if ( $group ) {
			$this->response( $group, RestController::HTTP_OK );
		} else {
			$this->response( [ 'status' => false, 'message' => 'No users were found' ], RestController::HTTP_BAD_REQUEST );
		}
	}

	public function user_groups_post() {
		$data = $this->post();
		$groups = $this->usersGroups->allJoinUser(['ug.idGroup' => $data['id']]);

		if ( $groups ) {
			$this->response( $groups, RestController::HTTP_OK );
		} else {
			$this->response( [ 'status' => false, 'message' => 'No users were found' ], RestController::HTTP_BAD_REQUEST );
		}
	}

	public function new_usergroup_post() {
		$data = $this->post();

		if ($this->usersGroups->search(['idUser' => $data['idUser'], 'idGroup' => $data['idGroup']])) {
			$this->response( [ 'status' => false, 'message' => 'El alumno ya esta en este grupo' ], RestController::HTTP_BAD_REQUEST );
		}else{
			if ($this->usersGroups->insert($data)) {
				$this->response( [ 'status' => true, 'message' => 'Se agrego un nuevo alumno.' ], RestController::HTTP_OK );
			} else {
				$this->response( [ 'status' => false, 'message' => 'No se pudo agregar al alumno' ], RestController::HTTP_BAD_REQUEST );
			}
		}
	}


	public function new_admin_post() {
		$data = $this->post();

		if ($this->admins->search(['usuario' => $data['usuario']])) {
			$this->response( [ 'status' => false, 'message' => 'El usuario ya existe' ], RestController::HTTP_BAD_REQUEST );
		}else{
			$data['password'] = md5($data['password']);

			$admin_id = $this->admins->insert($data);

			if ( $admin_id > 0 ) {
				$this->response( $admin_id, RestController::HTTP_OK );
			} else {
				$this->response( [ 'status' => false, 'message' => 'No users were found' ], RestController::HTTP_BAD_REQUEST );
			}
		}
	}

	public function search_admin_post() {
		$data = $this->post();
		$data['password'] = md5($data['password']);
		$admin = $this->admins->search($data);
		if ( $admin ) {
			$this->response( $admin, RestController::HTTP_OK );
		} else {
			$this->response( [ 'status' => false, 'message' => 'Usuario o contraseña incorrectos.' ], RestController::HTTP_BAD_REQUEST );
		}
	}


	public function get_payments_post() {
		$data = $this->post();
		$paymentsInfo = new stdClass;
		$paymentsInfo->curso = $this->summary->search(['id' => $data['idSummary']]);
		$paymentsInfo->grupo = $this->groups->search(['id' => $data['idGroup']]);
		$paymentsInfo->alumno = $this->users->search(['id' => $data['idUser']]);
		$paymentsInfo->pagos = $this->payments->all(['idSummary' => $data['idSummary'], 'idGroup' => $data['idGroup'], 'idUser' => $data['idUser']]);

		if ( $paymentsInfo ) {
			$this->response( $paymentsInfo, RestController::HTTP_OK );
		} else {
			$this->response( [ 'status' => false, 'message' => 'El usuario no tiene pagos registrados.' ], RestController::HTTP_BAD_REQUEST );
		}
	}

	public function dashboard_get() {
		$dashboardInfo = new stdClass;
		$dashboardInfo->cursos = $this->summary->all(['activo' => 1]);
		$dashboardInfo->grupos = $this->groups->all(['activo' => 1]);
		$dashboardInfo->alumnos = $this->users->all(['activo' => 1]);

		if ( $dashboardInfo ) {
			$this->response( $dashboardInfo, RestController::HTTP_OK );
		} else {
			$this->response( [ 'status' => false, 'message' => 'No hay información para mostrar.' ], RestController::HTTP_BAD_REQUEST );
		}
	}

	public function add_payment_post() {
		$data = $this->post();

		$payment_id = $this->payments->insert($data);

		if ( $payment_id > 0 ) {
			$this->response( [ 'status' => true, 'message' => 'El pago se guardo correctamente!' ], RestController::HTTP_OK );
		} else {
			$this->response( [ 'status' => false, 'message' => 'No se pudo guardar el pago' ], RestController::HTTP_BAD_REQUEST );
		}
	}

	public function new_pdf_post() {
		$data = $this->post();
		$pdf = new FPDF('L','mm','A4');
		$pdf->AddPage();
		$pdf->SetFont('Arial','I',20);
		$pdf->Cell(40,40,'Hola, Mundo!');
		$pdf->Output();

		/*$payment_id = $this->payments->insert($data);

		if ( $payment_id > 0 ) {
			$this->response( [ 'status' => true, 'message' => 'El pago se guardo correctamente!' ], RestController::HTTP_OK );
		} else {
			$this->response( [ 'status' => false, 'message' => 'No se pudo guardar el pago' ], RestController::HTTP_BAD_REQUEST );
		}*/
	}




	public function pdf_get() {
		$pdf = new FPDF('L','mm','A4');
		$pdf->AddPage();
		$pdf->SetFont('Arial','I',20);
		$pdf->Cell(40,40,'Hola, Mundo!');
		$pdf->Output();
	}


	/* FUNCIONES PRIVADAS */


	function _transform_alias($word){
		$word = strtolower($word);
		$search  = array('á', 'é', 'í', 'ó', 'ú');
		$replace = array('a', 'e', 'i', 'o', 'u');
		$alias = str_replace(' ', '_', $word);
		$alias = str_replace($search, $replace, $alias);
		return $alias;
	}



	// public function user_payments_post() {
	// 	$data = $this->post();
	// 	$payments = $this->payments->allJoinGroupSummary(['p.idUser' => $data['idUser'], 'p.idGroup' => $data['idGroup'], 'p.idSummary' => $data['idSummary']]);
	//
	// 	if ($payments) {
	// 		$this->response($payments, RestController::HTTP_OK);
	// 	} else {
	// 		$this->response('No se encontraron los datos.', RestController::HTTP_BAD_REQUEST);
	// 	}
	// }
}
