<?php
namespace Opencart\Catalog\Model\Extension\SoEntry\Module;
class SoAdvancedSearch extends \Opencart\System\Engine\Model {

	public function getMakes($data = array()) {
		$sql = "SELECT * FROM `".DB_PREFIX."so_make` ORDER BY `make_name` ASC";
		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getModels($data = array()) {
		$sql = "SELECT * FROM `".DB_PREFIX."so_model`";
		if (isset($data['make_id']) && !empty($data['make_id'])) {
			$sql .= " WHERE make_id = '".(int)$data['make_id']."'";
		}
		$sql .= " ORDER BY model_name ASC";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getEngines($data = array()) {
		$sql = "SELECT * FROM `".DB_PREFIX."so_engine`";
		if (isset($data['model_id']) && !empty($data['model_id'])) {
			$sql .= " WHERE model_id = '".(int)$data['model_id']."'";
		}
		$sql .= " ORDER BY `engine_name` ASC";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getYears($data = array()) {
		$sql = "SELECT * FROM `".DB_PREFIX."so_year`";
		if (isset($data['engine_id']) && !empty($data['engine_id'])) {
			$sql .= " WHERE engine_id = '".(int)$data['engine_id']."'";
		}
		$sql .= " ORDER BY `year_name` ASC";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getProducts($data = array()) {
		$sql = "SELECT p.product_id, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
			}

			if (!empty($data['filter_filter'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
			}
		} else {
			$sql .= " FROM " . DB_PREFIX . "product p";
		}

		if (isset($data['filter_make_id']) && !empty($data['filter_make_id'])) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "so_product_to_mmy spm ON (spm.product_id = p.product_id) LEFT JOIN " . DB_PREFIX ."so_make sm ON (sm.make_id = spm.make_id)";

			if (isset($data['filter_model_id']) && !empty($data['filter_model_id'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX ."so_model smo ON (smo.model_id = spm.model_id)";

				if (isset($data['filter_engine_id']) && !empty($data['filter_engine_id'])) {
					$sql .= " LEFT JOIN " . DB_PREFIX ."so_engine se ON (se.engine_id = spm.engine_id)";

					if (isset($data['filter_year_id']) && !empty($data['filter_year_id'])) {
						$sql .= " LEFT JOIN " . DB_PREFIX ."so_year sy ON (sy.year_id = spm.year_id)";
					}
				}
			}
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}

			if (!empty($data['filter_filter'])) {
				$implode = array();

				$filters = explode(',', $data['filter_filter']);

				foreach ($filters as $filter_id) {
					$implode[] = (int)$filter_id;
				}

				$sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
			}
		}

		if (isset($data['filter_make_id']) && !empty($data['filter_make_id'])) {
			$sql .= " AND spm.make_id = '".(int)$data['filter_make_id']."'";
		}

		if (isset($data['filter_model_id']) && !empty($data['filter_model_id'])) {
			$sql .= " AND spm.model_id = '".(int)$data['filter_model_id']."'";
		}

		if (isset($data['filter_engine_id']) && !empty($data['filter_engine_id'])) {
			$sql .= " AND spm.engine_id = '".(int)$data['filter_engine_id']."'";
		}

		if (isset($data['filter_year_id']) && !empty($data['filter_year_id'])) {
			$sql .= " AND spm.year_id = '".(int)$data['filter_year_id']."'";
		}

		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_name'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}

			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_tag'])));

				foreach ($words as $word) {
					$implode[] = "pd.tag LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			$sql .= ")";
		}

		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}

		if (isset($data['filter_make_id']) && !empty($data['filter_make_id'])) {
			$sql .= " GROUP BY spm.product_id";
		}
		else {
			$sql .= " GROUP BY p.product_id";
		}

		$sort_data = array(
			'pd.name',
			'p.model',
			'p.quantity',
			'p.price',
			'rating',
			'p.sort_order',
			'p.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} elseif ($data['sort'] == 'p.price') {
				$sql .= " ORDER BY (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY p.sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(pd.name) DESC";
		} else {
			$sql .= " ASC, LCASE(pd.name) ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$product_data = array();

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
		}

		return $product_data;
	}

	public function getTotalProducts($data = array()) {
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
			}

			if (!empty($data['filter_filter'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
			}
		} else {
			$sql .= " FROM " . DB_PREFIX . "product p";
		}

		if (isset($data['filter_make_id']) && !empty($data['filter_make_id'])) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "so_product_to_mmy spm ON (spm.product_id = p.product_id) LEFT JOIN " . DB_PREFIX ."so_make sm ON (sm.make_id = spm.make_id)";

			if (isset($data['filter_model_id']) && !empty($data['filter_model_id'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX ."so_model smo ON (smo.model_id = spm.model_id)";

				if (isset($data['filter_engine_id']) && !empty($data['filter_engine_id'])) {
					$sql .= " LEFT JOIN " . DB_PREFIX ."so_engine se ON (se.engine_id = spm.engine_id)";

					if (isset($data['filter_year_id']) && !empty($data['filter_year_id'])) {
						$sql .= " LEFT JOIN " . DB_PREFIX ."so_year sy ON (sy.year_id = spm.year_id)";
					}
				}
			}
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}

			if (!empty($data['filter_filter'])) {
				$implode = array();

				$filters = explode(',', $data['filter_filter']);

				foreach ($filters as $filter_id) {
					$implode[] = (int)$filter_id;
				}

				$sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
			}
		}

		if (isset($data['filter_make_id']) && !empty($data['filter_make_id'])) {
			$sql .= " AND spm.make_id = '".(int)$data['filter_make_id']."'";
		}

		if (isset($data['filter_model_id']) && !empty($data['filter_model_id'])) {
			$sql .= " AND spm.model_id = '".(int)$data['filter_model_id']."'";
		}

		if (isset($data['filter_engine_id']) && !empty($data['filter_engine_id'])) {
			$sql .= " AND spm.engine_id = '".(int)$data['filter_engine_id']."'";
		}

		if (isset($data['filter_year_id']) && !empty($data['filter_year_id'])) {
			$sql .= " AND spm.year_id = '".(int)$data['filter_year_id']."'";
		}

		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_name'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}

			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_tag'])));

				foreach ($words as $word) {
					$implode[] = "pd.tag LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			$sql .= ")";
		}

		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}
}