import React, { useState, useEffect } from 'react';
import { API_BASE, UPLOAD_BASE, MODELS_BASE, APP_CONFIG, API_ENDPOINTS } from './config';

import HomePage from './components/HomePage';
import EndangeredAnimalsPage from './components/EndangeredAnimalsPage';

import AboutPage from './components/AboutPage';

const AnimalShowcase3D = () => {
  const [selectedAnimal, setSelectedAnimal] = useState(null);
  const [searchTerm, setSearchTerm] = useState('');
  const [animals, setAnimals] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  
  // Thêm state cho bộ lọc
  const [filters, setFilters] = useState({
    conservation: 'all', // 'all', 'Đã tuyệt chủng', 'Cực kỳ nguy cấp', etc.
    speciesSort: 'none', // 'none', 'a-z', 'z-a', 'long-short', 'short-long'
    habitatType: 'all' // 'all', 'rừng', 'biển', 'đảo', etc.
  });
  const [showFilters, setShowFilters] = useState(false);
  
  // State cho navigation - lưu trong localStorage
  const [currentPage, setCurrentPage] = useState(() => {
    return localStorage.getItem('currentPage') || 'home';
  });

  // Helper functions for data mapping
  const getRegionDisplayName = (region) => {
    const region_mapping = {
      'Rừng nhiệt đới': 'Trên cạn',
      'Savanna': 'Trên cạn', 
      'Rừng taiga': 'Trên cạn',
      'Sa mạc': 'Trên cạn',
      'Đồng cỏ': 'Trên cạn',
      'Ocean': 'Dưới biển',
      'Forest': 'Trên cạn',
      'Desert': 'Trên cạn',
      'Grassland': 'Trên cạn',
      'Mountain': 'Trên cạn',
      'Wetland': 'Trên cạn',
      'Arctic': 'Trên cạn'
    };
    return region_mapping[region] || region || 'Chưa cập nhật';
  };

  const getConservationDisplayName = (status) => {
    const status_mapping = {
      'Least Concern': 'Ít quan ngại',
      'Near Threatened': 'Gần bị đe dọa',
      'Vulnerable': 'Dễ bị tổn thương',
      'Endangered': 'Nguy cấp',
      'Critically Endangered': 'Cực kỳ nguy cấp',
      'Extinct': 'Đã tuyệt chủng',
      'Data Deficient': 'Thiếu dữ liệu',
      'Not Evaluated': 'Chưa đánh giá'
    };
    return status_mapping[status] || status || 'Chưa cập nhật';
  };

  // Load animals from API
  useEffect(() => {
    loadAnimals();
  }, []);

  // Lưu currentPage vào localStorage khi thay đổi
  useEffect(() => {
    localStorage.setItem('currentPage', currentPage);
  }, [currentPage]);

  const loadAnimals = async () => {
    try {
      setLoading(true);
      setError(null);
      
      console.log('🔄 Đang kết nối API PHP...');
              console.log('API URL:', `${API_BASE}/animals_api_simple.php`);
      
              // Sử dụng API mới đã chuẩn hóa
        const response = await fetch(`${API_BASE}/animals_api_simple.php`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json'
        }
      });
      
      console.log('Response status:', response.status);
      console.log('Response headers:', response.headers);
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      
      const result = await response.json();
      console.log('API Response:', result);
      
      if (result.success && Array.isArray(result.data)) {
        // Xử lý dữ liệu từ database chuẩn hóa mới
        const processedAnimals = result.data.map(animal => ({
          ...animal,
          // Thêm các trường cần thiết cho frontend
          endangered: animal.conservation_status_name?.includes('Endangered') || animal.conservation_status_name?.includes('Critically Endangered'),
          habitat: getRegionDisplayName(animal.region_name) || 'Chưa cập nhật',
          population: animal.population_count || 'Không xác định',
          conservation_status: getConservationDisplayName(animal.conservation_status_name) || 'Chưa cập nhật',
          // Kiểm tra model 3D có sẵn
          has3DModel: animal.models && animal.models.length > 0,
          // Thêm images và models từ API
          images: animal.images || [],
          models: animal.models || []
        }));
        
        setAnimals(processedAnimals);
        setError(null);
        console.log(`✅ Đã tải thành công ${processedAnimals.length} động vật từ database chuẩn hóa`);
      } else if (result.error) {
        throw new Error('API Error: ' + result.error);
      } else {
        throw new Error('Dữ liệu không hợp lệ từ API - Expected array but got: ' + typeof result);
      }
      
    } catch (err) {
      console.error('❌ Error loading animals:', err);
      
      // Hiển thị error message cụ thể
      if (err.message.includes('Failed to fetch')) {
        setError('🔌 Không thể kết nối với backend - kiểm tra XAMPP và CORS configuration');
      } else if (err.message.includes('CORS')) {
        setError('🌐 Lỗi CORS - kiểm tra cấu hình backend PHP');
      } else {
        setError(err.message);
      }
      
      setAnimals([]); // Không có dữ liệu nếu lỗi
    } finally {
      setLoading(false);
    }
  };

  // Function để kiểm tra model 3D có sẵn cho động vật
  const check3DModelAvailability = async (animalId) => {
    try {
      console.log(`Đang kiểm tra model 3D cho động vật ID: ${animalId}`);
      
      // Sử dụng fetch đơn giản
      const response = await fetch(`${API_BASE}/animals/${animalId}`);
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      
      const result = await response.json();
      
      if (result && result.model_path) {
        console.log('3D model available for this animal');
        // Refresh animals list để cập nhật UI
        await loadAnimals();
      } else {
        console.log('No 3D model available for this animal');
      }
    } catch (error) {
      console.error('Error checking 3D model availability:', error);
    }
  };

  // Function để cập nhật trạng thái động vật
  const updateAnimalStatus = (animalId, newStatus) => {
    setAnimals(prevAnimals => 
      prevAnimals.map(animal => 
        animal.id == animalId 
          ? { ...animal, status_3d: newStatus }
          : animal
      )
    );
  };

  // Không còn cần mock data - sử dụng dữ liệu thực từ database PHP

  // Filter animals based on search term and filters
  let filteredAnimals = animals.filter(animal => {
    const matchesSearch = animal.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         animal.species_name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         (animal.description && animal.description.toLowerCase().includes(searchTerm.toLowerCase()));
    
    const matchesConservation = filters.conservation === 'all' || 
                               animal.conservation_status_name === filters.conservation;
    
    const matchesHabitatType = filters.habitatType === 'all' || 
                              (animal.region_name && animal.region_name.toLowerCase().includes(filters.habitatType.toLowerCase()));
    
    return matchesSearch && matchesConservation && matchesHabitatType;
  });

  // Sort animals based on species sort filter
  if (filters.speciesSort !== 'none') {
    filteredAnimals = [...filteredAnimals].sort((a, b) => {
      const nameA = a.species_name || '';
      const nameB = b.species_name || '';
      
      switch (filters.speciesSort) {
        case 'a-z':
          return nameA.localeCompare(nameB);
        case 'z-a':
          return nameB.localeCompare(nameA);
        case 'long-short':
          return nameB.length - nameA.length;
        case 'short-long':
          return nameA.length - nameB.length;
        default:
          return 0;
      }
    });
  }



  const getImageUrl = (animalId) => {
    // Tìm động vật theo ID và lấy hình ảnh chính
    const animal = animals.find(a => a.id === animalId);
    console.log(`🔍 Tìm hình ảnh cho động vật ID ${animalId}:`, animal);
    
    if (animal?.media && Array.isArray(animal.media)) {
      console.log(`📸 Media của động vật ${animalId}:`, animal.media);
      
      const primaryImage = animal.media.find(m => m.media_type === 'image' && m.is_primary);
      if (primaryImage?.file_url) {
        console.log(`✅ Hình ảnh chính: ${primaryImage.file_url}`);
        return primaryImage.file_url;
      }
      
      // Nếu không có primary, lấy hình đầu tiên
      const firstImage = animal.media.find(m => m.media_type === 'image');
      if (firstImage?.file_url) {
        console.log(`✅ Hình ảnh đầu tiên: ${firstImage.file_url}`);
        return firstImage.file_url;
      }
      
      console.log(`❌ Không tìm thấy hình ảnh cho động vật ${animalId}`);
    } else {
      console.log(`❌ Không có media cho động vật ${animalId}`);
    }
    
    // Fallback - sử dụng hình ảnh mặc định
    return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjMzMzIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iI2ZmZiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg==';
  };

  const getModelUrl = (animalId) => {
    // Tìm động vật theo ID và lấy model 3D
    const animal = animals.find(a => a.id === animalId);
    console.log(`🔍 Tìm model 3D cho động vật ID ${animalId}:`, animal);
    
    if (animal?.media && Array.isArray(animal.media)) {
      const model3D = animal.media.find(m => m.media_type === '3d_model');
      if (model3D?.file_url) {
        console.log(`✅ Model 3D: ${model3D.file_url}`);
        return model3D.file_url;
      }
      console.log(`❌ Không tìm thấy model 3D cho động vật ${animalId}`);
    } else {
      console.log(`❌ Không có media cho động vật ${animalId}`);
    }
    
    return null;
  };

  const getUniqueSpecies = () => {
    return [...new Set(animals.map(animal => animal.species_name).filter(Boolean))];
  };

  const getUniqueRegions = () => {
    return [...new Set(animals.map(animal => animal.region_name).filter(Boolean))];
  };

  const getConservationColor = (conservationStatus) => {
    if (!conservationStatus) return '#999999';
    
    // Sử dụng color_code từ database hoặc fallback
    const animal = animals.find(a => a.conservation_status_name === conservationStatus);
    if (animal?.conservation_status_color) {
      return animal.conservation_status_color;
    }
    
    // Fallback colors
    if (conservationStatus?.includes('nguy cấp') || conservationStatus?.includes('tuyệt chủng')) {
      return '#FF0000'; // Red
    } else if (conservationStatus?.includes('sắp nguy cấp')) {
      return '#FF6600'; // Orange
    } else if (conservationStatus?.includes('ít quan tâm')) {
      return '#00CC00'; // Green
    } else {
      return '#999999'; // Gray
    }
  };



  const resetFilters = () => {
    setFilters({
      conservation: 'all',
      speciesSort: 'none',
      habitatType: 'all'
    });
    setSearchTerm('');
  };

  // Render loading state
  if (loading) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900 flex items-center justify-center">
        <div className="text-center text-white">
          <div className="animate-spin rounded-full h-16 w-16 border-b-2 border-white mx-auto mb-4"></div>
          <p className="text-xl">Đang tải dữ liệu động vật...</p>
        </div>
      </div>
    );
  }

  // Render error state
  if (error && animals.length === 0) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900 flex items-center justify-center">
        <div className="text-center text-white max-w-md mx-auto p-6">
          <div className="text-6xl mb-4">⚠️</div>
          <h2 className="text-2xl font-bold mb-4">Lỗi Kết Nối</h2>
          <p className="text-white/80 mb-6">{error}</p>
          <button
            onClick={loadAnimals}
            className="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200"
          >
            🔄 Thử lại
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
      {/* Navigation */}
      <nav className="bg-white/10 backdrop-blur-lg border-b border-white/20">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            <div className="flex items-center space-x-8">
              <h1 className="text-2xl font-bold text-white">{APP_CONFIG.name}</h1>
              
              <div className="hidden md:flex space-x-6">
                <button
                  onClick={() => setCurrentPage('home')}
                  className={`px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 ${
                    currentPage === 'home' 
                      ? 'bg-white/20 text-white' 
                      : 'text-white/60 hover:text-white hover:bg-white/10'
                  }`}
                >
                  🏠 Trang chủ
                </button>
                <button
                  onClick={() => setCurrentPage('endangered')}
                  className={`px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 ${
                    currentPage === 'endangered' 
                      ? 'bg-white/20 text-white' 
                      : 'text-white/60 hover:text-white hover:bg-white/10'
                  }`}
                >
                  🚨 Động vật quý hiếm
                </button>
                
                

                <button
                  onClick={() => setCurrentPage('about')}
                  className={`px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 ${
                    currentPage === 'about' 
                      ? 'bg-white/20 text-white' 
                      : 'text-white/60 hover:text-white hover:bg-white/10'
                  }`}
                >
                  ℹ️ Giới thiệu
                </button>
              </div>
            </div>
            
            <div className="flex items-center space-x-4">
              {/* Admin link removed - using local 3D models */}
            </div>
          </div>
        </div>
      </nav>

      {/* Main Content */}
      <main className="flex-1">
        {currentPage === 'home' && (
          <HomePage
            animals={animals}
            selectedAnimal={selectedAnimal}
            setSelectedAnimal={setSelectedAnimal}
            searchTerm={searchTerm}
            setSearchTerm={setSearchTerm}
            filters={filters}
            setFilters={setFilters}
            showFilters={showFilters}
            setShowFilters={setShowFilters}
            getImageUrl={getImageUrl}
            getModelUrl={getModelUrl}
            getUniqueSpecies={getUniqueSpecies}
            getUniqueHabitats={getUniqueRegions}
            getConservationColor={getConservationColor}
            resetFilters={resetFilters}
            filteredAnimals={filteredAnimals}
            API_BASE={API_BASE}
          />
        )}
        
        {currentPage === 'endangered' && (
          <EndangeredAnimalsPage
            animals={animals}
            selectedAnimal={selectedAnimal}
            setSelectedAnimal={setSelectedAnimal}
            getImageUrl={getImageUrl}
            getModelUrl={getModelUrl}
            getConservationColor={getConservationColor}
            onNavigateToHome={() => setCurrentPage('home')}
          />
        )}
        

        
        {currentPage === 'about' && (
          <AboutPage />
        )}
        

        

      </main>

      {/* Footer */}
      <footer className="bg-gradient-to-r from-purple-900/20 via-blue-900/20 to-indigo-900/20 backdrop-blur-lg border-t border-white/20 mt-16">
        <div className="max-w-7xl mx-auto px-4 py-12">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            {/* Brand Section */}
            <div className="text-center md:text-left">
              <h3 className="text-2xl font-bold text-white mb-4 flex items-center justify-center md:justify-start">
                <span className="mr-2">🌍</span>
                {APP_CONFIG.name}
              </h3>
              <p className="text-white/70 text-sm leading-relaxed">
                Khám phá thế giới động vật với công nghệ 3D AI tiên tiến. 
                Bảo vệ và bảo tồn thiên nhiên cho thế hệ tương lai.
              </p>
            </div>

            {/* Quick Links */}
            <div className="text-center md:text-left">
              <h4 className="text-lg font-semibold text-white mb-4">Liên kết nhanh</h4>
              <div className="space-y-2">
                <button 
                  onClick={() => setCurrentPage('home')}
                  className="block text-white/70 hover:text-white transition-colors duration-200 text-sm"
                >
                  🏠 Trang chủ
                </button>
                <button 
                  onClick={() => setCurrentPage('endangered')}
                  className="block text-white/70 hover:text-white transition-colors duration-200 text-sm"
                >
                  🚨 Động vật quý hiếm
                </button>
                <button 
                  onClick={() => setCurrentPage('about')}
                  className="block text-white/70 hover:text-white transition-colors duration-200 text-sm"
                >
                  ℹ️ Giới thiệu
                </button>
              </div>
            </div>

            {/* Technology Stack */}
            <div className="text-center md:text-left">
              <h4 className="text-lg font-semibold text-white mb-4">Công nghệ</h4>
              <div className="flex flex-wrap justify-center md:justify-start gap-2">
                <span className="px-3 py-1 bg-blue-500/20 text-blue-300 rounded-full text-xs font-medium">
                  React
                </span>
                <span className="px-3 py-1 bg-green-500/20 text-green-300 rounded-full text-xs font-medium">
                  Three.js
                </span>
                <span className="px-3 py-1 bg-purple-500/20 text-purple-300 rounded-full text-xs font-medium">
                  PHP
                </span>
                <span className="px-3 py-1 bg-orange-500/20 text-orange-300 rounded-full text-xs font-medium">
                  MySQL
                </span>
              </div>
            </div>
          </div>

          {/* Divider */}
          <div className="border-t border-white/10 mb-6"></div>

          {/* Copyright */}
          <div className="text-center">
            <p className="text-white/60 text-sm mb-2">
              &copy; 2025 {APP_CONFIG.name}. Phiên bản {APP_CONFIG.version}
            </p>
            <p className="text-white/50 text-xs">
              Được phát triển với ❤️ để bảo vệ thiên nhiên
            </p>
          </div>
        </div>
      </footer>
    </div>
  );
};

export default AnimalShowcase3D;