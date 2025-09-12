import React, { useEffect, useRef } from 'react';
import AnimalCard from './AnimalCard';

const HomePage = ({ animals, selectedAnimal, setSelectedAnimal, searchTerm, setSearchTerm, filters, setFilters, showFilters, setShowFilters, getImageUrl, getModelUrl, getUniqueSpecies, getUniqueHabitats, getConservationColor, resetFilters, filteredAnimals, API_BASE }) => {
  const selectedAnimalRef = useRef(null);

  // Scroll to selected animal when it changes
  useEffect(() => {
    if (selectedAnimal && selectedAnimalRef.current) {
      selectedAnimalRef.current.scrollIntoView({ 
        behavior: 'smooth', 
        block: 'center' 
      });
    }
  }, [selectedAnimal]);

  return (
    <>
      {/* Hero Section */}
      <div className="relative overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-r from-purple-600/20 to-blue-600/20"></div>
        <div className="relative max-w-7xl mx-auto px-4 py-16 sm:px-6 lg:px-8">
          <div className="text-center text-white z-10 animate-fade-in-up">
            <h2 className="text-5xl font-bold mb-4 animate-gradient-text">
              Khám Phá Thế Giới Động Vật 3D
            </h2>
            <p className="text-xl text-white/80 mb-4 animate-fade-in-delay-1">
              Hành trình tuyệt vời qua vương quốc thiên nhiên
            </p>
            <p className="text-white/60 animate-fade-in-delay-2">
              Với các model 3D được tạo sẵn từ admin
            </p>
          </div>
        </div>
      </div>

      {/* Search Bar */}
      <div className="max-w-7xl mx-auto px-4 py-8">
        <div className="relative max-w-md mx-auto animate-fade-in-up-delay-3">
          <span className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">🔍</span>
          <input
            type="text"
            placeholder="Tìm kiếm động vật..."
            className="w-full pl-10 pr-4 py-3 bg-white/10 backdrop-blur-lg border border-white/20 rounded-full text-white placeholder-white/60 focus:outline-none focus:border-pink-400 focus:shadow-lg focus:shadow-pink-400/25 transition-all duration-300 search-glow"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </div>
      </div>

      {/* Statistics Cards */}
      <div className="max-w-7xl mx-auto px-4 pb-8">
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div className="bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl p-4 text-center">
            <div className="text-3xl mb-2">🦊</div>
            <div className="text-2xl font-bold text-white">{animals.length}</div>
            <div className="text-white/60 text-sm">Tổng số động vật</div>
          </div>
          <div className="bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl p-4 text-center">
            <div className="text-3xl mb-2">🌶️</div>
            <div className="text-2xl font-bold text-white">{filteredAnimals.filter(a => a.endangered).length}</div>
            <div className="text-white/60 text-sm">Trong danh sách đã</div>
          </div>
          <div className="bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl p-4 text-center">
            <div className="text-3xl mb-2">🎯</div>
            <div className="text-2xl font-bold text-white">{animals.filter(a => a.has3DModel).length}</div>
            <div className="text-white/60 text-sm">Có model 3D</div>
          </div>
          <div className="bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl p-4 text-center">
            <div className="text-3xl mb-2">🌍</div>
            <div className="text-2xl font-bold text-white">{getUniqueHabitats().length}</div>
            <div className="text-white/60 text-sm">Khu vực sống</div>
          </div>
        </div>
      </div>

      {/* Advanced Filters */}
      <div className="max-w-7xl mx-auto px-4 pb-6">
        <div className="flex flex-col items-center space-y-4">
          {/* Filter Toggle Button */}
          <button
            onClick={() => setShowFilters(!showFilters)}
            className="flex items-center space-x-2 px-6 py-3 bg-white/10 backdrop-blur-lg border border-white/20 rounded-full text-white hover:bg-white/20 transition-all duration-300 hover:scale-105"
          >
            <span>🔧</span>
            <span>{showFilters ? 'Ẩn bộ lọc' : 'Hiện bộ lọc nâng cao'}</span>
            <span className={`transform transition-transform duration-300 ${showFilters ? 'rotate-180' : ''}`}>▼</span>
          </button>

          {/* Filter Panel */}
          {showFilters && (
            <div className="w-full max-w-4xl bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl p-6 animate-fade-in-up">
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {/* Conservation Status Filter */}
                <div>
                  <label className="block text-white/80 text-sm font-medium mb-2">🦁 Tình trạng bảo tồn</label>
                  <select
                    value={filters.conservation}
                    onChange={(e) => setFilters({...filters, conservation: e.target.value})}
                    className="w-full px-3 py-2 bg-white/20 backdrop-blur-lg border border-white/30 rounded-lg text-white focus:outline-none focus:border-pink-400 transition-all duration-300"
                    style={{
                      color: 'white',
                      backgroundColor: 'rgba(255, 255, 255, 0.2)'
                    }}
                  >
                    <option value="all" style={{color: 'white', backgroundColor: '#374151'}}>Tất cả tình trạng</option>
                    <option value="Đã tuyệt chủng" style={{color: 'white', backgroundColor: '#374151'}}>🖤 Đã tuyệt chủng</option>
                    <option value="Cực kỳ nguy cấp" style={{color: 'white', backgroundColor: '#374151'}}>🔴 Cực kỳ nguy cấp</option>
                    <option value="Nguy cấp" style={{color: 'white', backgroundColor: '#374151'}}>🟠 Nguy cấp</option>
                    <option value="Dễ bị tổn thương" style={{color: 'white', backgroundColor: '#374151'}}>🟡 Dễ bị tổn thương</option>
                    <option value="Gần bị đe dọa" style={{color: 'white', backgroundColor: '#374151'}}>🟢 Gần bị đe dọa</option>
                    <option value="Ít quan ngại" style={{color: 'white', backgroundColor: '#374151'}}>🟢 Ít quan ngại</option>
                  </select>
                </div>

                {/* Species Sort Filter */}
                <div>
                  <label className="block text-white/80 text-sm font-medium mb-2">🐾 Sắp xếp tên tiếng Anh</label>
                  <select
                    value={filters.speciesSort}
                    onChange={(e) => setFilters({...filters, speciesSort: e.target.value})}
                    className="w-full px-3 py-2 bg-white/20 backdrop-blur-lg border border-white/30 rounded-lg text-white focus:outline-none focus:border-pink-400 transition-all duration-300"
                    style={{
                      color: 'white',
                      backgroundColor: 'rgba(255, 255, 255, 0.2)'
                    }}
                  >
                    <option value="none" style={{color: 'white', backgroundColor: '#374151'}}>Không sắp xếp</option>
                    <option value="a-z" style={{color: 'white', backgroundColor: '#374151'}}>A → Z (A đến Z)</option>
                    <option value="z-a" style={{color: 'white', backgroundColor: '#374151'}}>Z → A (Z đến A)</option>
                    <option value="long-short" style={{color: 'white', backgroundColor: '#374151'}}>Dài → Ngắn (Nhiều chữ đến ít chữ)</option>
                    <option value="short-long" style={{color: 'white', backgroundColor: '#374151'}}>Ngắn → Dài (Ít chữ đến nhiều chữ)</option>
                  </select>
                </div>



                {/* Habitat Type Filter */}
                <div>
                  <label className="block text-white/80 text-sm font-medium mb-2">🌍 Loại khu vực sống</label>
                  <select
                    value={filters.habitatType}
                    onChange={(e) => setFilters({...filters, habitatType: e.target.value})}
                    className="w-full px-3 py-2 bg-white/20 backdrop-blur-lg border border-white/30 rounded-lg text-white focus:outline-none focus:border-pink-400 transition-all duration-300"
                    style={{
                      color: 'white',
                      backgroundColor: 'rgba(255, 255, 255, 0.2)'
                    }}
                  >
                    <option value="all" style={{color: 'white', backgroundColor: '#374151'}}>Tất cả khu vực</option>
                    <option value="rừng" style={{color: 'white', backgroundColor: '#374151'}}>🌳 Rừng</option>
                    <option value="biển" style={{color: 'white', backgroundColor: '#374151'}}>🌊 Biển</option>
                    <option value="đảo" style={{color: 'white', backgroundColor: '#374151'}}>🏝️ Đảo</option>
                    <option value="đồng bằng" style={{color: 'white', backgroundColor: '#374151'}}>🌾 Đồng bằng</option>
                    <option value="trên không" style={{color: 'white', backgroundColor: '#374151'}}>☁️ Trên không</option>
                    <option value="hồ" style={{color: 'white', backgroundColor: '#374151'}}>🏞️ Hồ</option>
                    <option value="sông" style={{color: 'white', backgroundColor: '#374151'}}>🌊 Sông</option>
                  </select>
                </div>
              </div>

              {/* Reset Filters Button */}
              <div className="mt-6 text-center">
                <button
                  onClick={resetFilters}
                  className="px-6 py-2 bg-red-600/20 hover:bg-red-600/30 text-red-300 border border-red-500/30 rounded-lg transition-all duration-300 hover:scale-105"
                >
                  🔄 Đặt lại bộ lọc
                </button>
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Filter Actions */}
      <div className="max-w-7xl mx-auto px-4">
        <div className="flex justify-center space-x-4 mt-6">
          <div className="text-center mt-6">
            <button
              onClick={resetFilters}
              className="bg-gray-600/30 hover:bg-gray-600/50 text-white rounded-lg transition-all duration-300 px-6 py-2 mr-4"
            >
              Đặt lại
            </button>
            <button
              onClick={resetFilters}
              className="bg-red-600/20 hover:bg-red-600/30 text-red-300 border border-red-500/30 rounded-lg transition-all duration-300 px-6 py-2"
            >
              Đặt lại bộ lọc
            </button>
          </div>
        </div>
        
        {/* Results Count */}
        <div className="text-white/60 text-sm flex items-center space-x-2 justify-center mt-4">
          <span></span>
          <span>Kết quả: {filteredAnimals.length} / {animals.length}</span>
        </div>
      </div>




      {/* Animals Grid */}
      <div className="max-w-7xl mx-auto px-4 pb-16">
        <div className="text-center mb-8">
          <h3 className="text-3xl font-bold text-white mb-2">
            🦁 Tất Cả Động Vật
          </h3>
          <p className="text-white/60">
            Khám phá thế giới động vật đa dạng
          </p>
        </div>
        
        {filteredAnimals.length > 0 ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            {filteredAnimals.map((animal, index) => (
              <div
                key={animal.id}
                ref={selectedAnimal && selectedAnimal.id === animal.id ? selectedAnimalRef : null}
                className={`animate-fade-in-up ${selectedAnimal && selectedAnimal.id === animal.id ? 'ring-4 ring-yellow-400 ring-opacity-50 rounded-2xl' : ''}`}
                style={{ animationDelay: `${index * 100}ms`, animationFillMode: 'both' }}
              >
                                 <AnimalCard
                   animal={animal}
                   API_BASE={API_BASE}
                   getImageUrl={getImageUrl}
                   getModelUrl={getModelUrl}
                   getConservationColor={getConservationColor}
                   onStatusUpdate={(animalId, newStatus) => {
                     // Reload animals khi status thay đổi
                     if (window.location.reload) {
                       window.location.reload();
                     }
                   }}
                 />
              </div>
            ))}
          </div>
        ) : (
          <div className="text-center py-16">
            <div className="text-6xl mb-4">🔍</div>
            <h3 className="text-2xl font-bold text-white mb-2">Không tìm thấy kết quả</h3>
            <p className="text-white/60">Thử tìm kiếm với từ khóa khác</p>
            <h3 className="text-2xl font-bold text-white mb-2">Không tìm thấy động vật</h3>
            <p className="text-white/60 mb-6">
              Không có động vật nào phù hợp với tiêu chí tìm kiếm của bạn
            </p>
            <button
              onClick={resetFilters}
              className="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200"
            >
              🔄 Đặt lại bộ lọc
            </button>
          </div>
        )}
      </div>

      {/* Conservation Education Section */}
      <div className="max-w-7xl mx-auto px-4 py-16">
        <div className="text-center mb-12">
          <h3 className="text-4xl font-bold text-white mb-4">
            🌿 Bảo Tồn Thiên Nhiên
          </h3>
          <p className="text-xl text-white/60">
            Hãy cùng chung tay bảo vệ các loài động vật quý hiếm
          </p>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {/* IUCN Red List */}
          <div className="bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl p-6 text-center hover:scale-105 transition-all duration-300">
            <div className="text-5xl mb-4">🎉</div>
            <h4 className="text-xl font-bold text-white mb-3">Danh Sách Đỏ IUCN</h4>
            <p className="text-white/70 text-sm mb-4">
              Hệ thống phân loại mức độ đe dọa của các loài động vật trên toàn thế giới
            </p>
            <div className="text-xs text-white/50 space-y-1">
              <div>🔴 Extinct - Tuyệt chủng</div>
              <div>🟠 Critically Endangered - Cực kỳ nguy cấp</div>
              <div>🟡 Endangered - Nguy cấp</div>
              <div>🟢 Vulnerable - Dễ bị tổn thương</div>
            </div>
          </div>

          {/* Habitat */}
          <div className="bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl p-6 text-center hover:scale-105 transition-all duration-300">
            <div className="text-5xl mb-4">🌎</div>
            <h4 className="text-xl font-bold text-white mb-3">Khu Vực Sống</h4>
            <p className="text-white/70 text-sm mb-4">
              Mỗi loài động vật có khu vực sống riêng biệt cần được bảo vệ
            </p>
            <div className="text-xs text-white/50 space-y-1">
              <div>🌳 Forest - Rừng</div>
              <div>🏜️ Savanna - Xavan</div>
              <div>🌊 Ocean - Đại dương</div>
              <div>🎋 Bamboo Forest - Rừng tre</div>
            </div>
          </div>

          {/* Conservation Actions */}
          <div className="bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl p-6 text-center hover:scale-105 transition-all duration-300">
            <div className="text-5xl mb-4">🌱</div>
            <h4 className="text-xl font-bold text-white mb-3">Hành Động Bảo Tồn</h4>
            <p className="text-white/70 text-sm mb-4">
              Những việc bạn có thể làm để bảo vệ động vật hoang dã
            </p>
            <div className="text-xs text-white/50 space-y-1">
              <div>💡 Tìm hiểu về các loài</div>
              <div>♻️ Giảm thiểu rác thải</div>
              <div>🌱 Trồng cây xanh</div>
              <div>📢 Lan tỏa thông điệp</div>
            </div>
          </div>
        </div>
      </div>

      {/* Call to Action */}
      <div className="max-w-7xl mx-auto px-4 py-16">
        <div className="text-center">
          <h3 className="text-3xl font-bold text-white mb-4">
            Bạn muốn thêm động vật mới?
          </h3>
          <p className="text-white/80 mb-8 max-w-2xl mx-auto">
            Sử dụng trang Admin để thêm động vật mới, upload hình ảnh và tạo model 3D tự động với AI
          </p>
          <a
            href="/admin"
            className="inline-block px-8 py-4 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold rounded-full transition-all duration-300 hover:scale-105 shadow-lg"
          >
            🚀 Vào trang Admin
          </a>
        </div>
      </div>
    </>
  );
};

export default HomePage;
