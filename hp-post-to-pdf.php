<?php
/*
Plugin Name: HP Post to PDF
Version: 0.0.1
Description: Converts a post or page to a PDF download using 
Licence: GPLv2
Author: Phil Halfpenny
	This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
if ( !class_exists( 'HPPostToPDF' ) ) {
	class HPPostToPDF {
		private $_fileName;

		function __construct( ) {
			if ( !is_admin( ) ) {
				add_action( 'wp', array( &$this, 'create_pdf' ) );
			}
		}

		function create_pdf( ) {
			if ( '1' == ( isset( $_GET['p2p_output'] ) ? $_GET['p2p_output'] : null ) ) {
				global $post;
				$post = get_post();

				$this->_fileName = $post->post_name . '.pdf';
				
				$this->convert_to_pdf( $post->ID );
			}
		}

		private function convert_to_pdf( $post_id ) {
			$post = get_post( $post_id );
			
			
			$post->post_content = wpautop( $post->post_content );
			$post->post_content = do_shortcode( $post->post_content );
			
			$html = '<div class="pdf_container">';
			if ( file_exists( get_stylesheet_directory( ) . '/pdf.css' ) ) {
				$html .= '<link href="' . get_stylesheet_directory_uri( ) . '/pdf.css" type="text/css" rel="stylesheet" />';
			}
			elseif ( file_exists( get_stylesheet_directory( ) . '/print.css' ) ) {
				$html .= '<link href="' . get_stylesheet_directory_uri( ) . '/print.css" type="text/css" rel="stylesheet" />';
			}
			else {
				$html .= '<link href="' . get_stylesheet_directory_uri( ) . '/style.css" type="text/css" rel="stylesheet" />';
			}
			
			$html .= htmlspecialchars_decode( htmlentities( $post->post_content, ENT_NOQUOTES, 'UTF-8', false ), ENT_NOQUOTES );

			$html .= '</div>';

			require_once dirname( __FILE__ ) . '/dompdf/dompdf_config.inc.php';

			$dompdf = new DOMPDF( );
			$dompdf->load_html( $html );
			$dompdf->render( );

			$dompdf->stream( $this->_fileName, array( 'Attachment' => true ) );
		}
	}

	$post_to_pdf = new HPPostToPDF( );
}